<!DOCTYPE html>
<?php
session_start();
error_reporting(E_ALL);

define("CONFIG_FILE", "includes/config.php");
define("SETUP_FILE", "includes/setup.php");
define("FUNCTIONS_FILE", "includes/functions.php");
define("BOOTSTRAP_FILE", 'includes/bootstrap.php');
define("MASTER_PASSWORD_MINLEN", 8);

require_once(FUNCTIONS_FILE);
?>

<?php

/* ───────────────────────────────────────────────────────────────────── */
/*                         Warn user about HTTPS                         */
/* ───────────────────────────────────────────────────────────────────── */
if (isSecure() !== True) {

  foreach ($_SERVER as $var => $val) {
    if (is_array($val)) {
      $val = json_encode($val, JSON_PRETTY_PRINT);
    }
    $server_vars[] = "<b>$var:</b> $val";
  }

  $https_url    = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  echo alert("
  Warning
  <hr>
  It seems you are currently not using HTTPS.<br>
  It is highly recommended you switch to using HTTPS for two main reasons:
  <ul>
    <li>When using HTTPS, your traffic is encrypted and can't be monitored on local networks</li>
    <li>You will be able to use the copy to clipboard function</li>
  </ul>
  <i>To ignore this warning, set <code>IGNORE_SSL_WARNING</code> in your <b>config.php</b> file to <code>True</code></i>
  <hr>
  <a href='{$https_url}' class='btn btn-success'>Switch to HTTPS</a>
  <button type='button' class='btn btn-warning' data-bs-toggle='collapse' data-bs-target='#debug' aria-label='Close'>Debug info</button>
  <button type='button' class='btn btn-danger' data-bs-dismiss='alert' aria-label='Close'>Dismiss</button>
  <div class='collapse my-2' id='debug'>
    <div class='card card-body bg-dark text-white'>
    <h5>Debug info</h5>
    <hr>
    ".implode("<br>", $server_vars)."
    </div>
  </div>
    ", "danger");
}

try {
  /* ────────────────────────────────────────────────────────────────────────── */
  /*            Verify that config exists, if not, require setup.php            */
  /* ────────────────────────────────────────────────────────────────────────── */
  if (!file_exists(CONFIG_FILE)) {
    die(require_once(SETUP_FILE));
  }
  
  if (empty(file_get_contents(CONFIG_FILE))) {
    die(require_once(SETUP_FILE));
  }
  
  /* ────────────────────────────────────────────────────────────────────────── */
  /*                          All good, config exists!                          */
  /* ────────────────────────────────────────────────────────────────────────── */
  require_once(CONFIG_FILE);
  require_once(BOOTSTRAP_FILE);
  
  if (!defined("SITE_TITLE")) {
    define("SITE_TITLE", "PHP Password Manager");
  }
  $title = SITE_TITLE;
  
  echo "<title>$title</title>";
} catch (Throwable $t) {
    echo alert("Setup couldn't run: $t");
}

?>

<?php 
  if (!defined("BACKGROUND_COLOR")) {
    define("BACKGROUND_COLOR", "#111");
  }
  echo "<body>";
?>
<div class="container" style="padding-top:10px;">
<?php
if (isset($_POST['mpassword'])) {
    $_GET['lock'] = null;
    $_SESSION['password'] = $_POST['mpassword'];
}

if (isset($_GET['lock'])) {
    $_SESSION['password'] = null;
    header("Location: index.php");
}

$pwInput = "Please enter master password: 
<form action='' method='POST' autocomplete='off'>
<input type='password' name='mpassword' class='form-control' autocomplete='off' spellcheck='false' autofocus>
</form>";

# TFA Dropdown
$tfa_dropdown = getTFA_dropdown();

# User is attempting to sign in, but password is incorrect
if (isset($_POST['mpassword'])) {
  $hpw = hash("sha512", $_POST['mpassword'].SALT);
  if ($hpw <> MASTER_PASSWORD) {
    echo alert("Invalid password", "danger");
    die($pwInput);
  }
}

# User is not signed in
if (!isset($_SESSION['password'])) {
    die($pwInput);
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                 DELETE                                */
/* ───────────────────────────────────────────────────────────────────── */
if (isset($_POST['del'])) {
  $id = mysqli_real_escape_string($sqlcon, $_POST['id']);
  $delete = $sqlcon->prepare("DELETE FROM accounts WHERE id = ?");
  $delete->bind_param("s", $id);
  $delete->execute();

  if ($delete->affected_rows > 0) {
    echo "<div class='alert alert-success'>Entry deleted.</div>";
  } else {
    echo "<div class='alert alert-danger'>Could not delete entry: " . $sqlcon->error . "</div>";
  }
  $delete->close();
  if ($delete) {
    echo "<div class='alert alert-success'>Entry deleted.</div>";
  } else {
    echo "<div class='alert alert-danger'>Could not delete entry: $sqlcon->error</div>";
  }
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                  EDIT                                 */
/* ───────────────────────────────────────────────────────────────────── */
if (isset($_POST['edit'])) {
  $id = mysqli_real_escape_string($sqlcon,$_POST['id']);
  $name = mysqli_real_escape_string($sqlcon,$_POST['name']);
  $username = mysqli_real_escape_string($sqlcon,$_POST['username']);
  $salt = passGen(32, 'lud');
  $iv = genIV();
  $password = (empty($_POST['password'])) ? "[EMPTY]" : $_POST['password'];
  $password = encrypt($password, $salt.ENCRYPTION_KEY, iv: $iv);
  $desc = mysqli_real_escape_string($sqlcon,$_POST['desc']);
  $url = mysqli_real_escape_string($sqlcon,$_POST['url']);
  $tfa = mysqli_real_escape_string($sqlcon, $_POST['2fa']);
  $tfa_id = mysqli_real_escape_string($sqlcon, $_POST['2fa_id']);
  # check if entry exists
  $exists = "SELECT * FROM accounts WHERE `id` = '$id'";
  $exists = mysqli_query($sqlcon, $exists);
  if ($exists->num_rows > 0) {
  $stmt = $sqlcon->prepare("UPDATE accounts SET `name` = ?, `username` = ?, `password` = ?, `salt` = ?, `iv` = ?, `url` = ?, `description` = ?, `2fa` = ?, `2fa_id` = ? WHERE id = ?");
  $stmt->bind_param("ssssssssis", $name, $username, $password, $salt, $iv, $url, $desc, $tfa, $id, $tfa_id);
  $edit = $stmt->execute();
  $stmt->close();
  if ($edit) {
    echo "<div class='alert alert-success'>Entry <b>$name</b> updated!</div>";
  } else {
    echo "<div class='alert alert-danger'>Could not update entry: $sqlcon->error</div>";
  }
} else {
  echo "<div class='alert alert-danger'>This entry (ID #$id) does not exist.</div>";
}
}

/* ───────────────────────────────────────────────────────────────────── */
/*                                  ADD                                  */
/* ───────────────────────────────────────────────────────────────────── */
if (isset($_POST['add'])) {
  $name = mysqli_real_escape_string($sqlcon, $_POST['name']);
  $username = mysqli_real_escape_string($sqlcon, $_POST['username']);
  $salt = passGen(32, 'lud');
  $iv = genIV();
  $password = (empty($_POST['password'])) ? "[EMPTY]" : $_POST['password'];
  $password = encrypt($password, $salt.ENCRYPTION_KEY, iv: $iv);
  $desc = mysqli_real_escape_string($sqlcon, $_POST['desc']);
  $url = mysqli_real_escape_string($sqlcon, $_POST['url']);
  $tfa = mysqli_real_escape_string($sqlcon, $_POST['2fa']);
  $stmt = $sqlcon->prepare("INSERT INTO accounts (`name`, `username`, `password`, `salt`, `iv`, `description`, `url`, `2fa`, `2fa_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssssss", $name, $username, $password, $salt, $iv, $desc, $url, $tfa, $tfa_id);
  $add = $stmt->execute();
  $stmt->close();
  if ($add) {
    echo "<div class='alert alert-success'>Entry added.</div>";
  } else {
    echo "<div class='alert alert-danger'>Entry couldn't be added: $sqlcon->error</div>";
  }
}

if (isset($_GET['reencrypt'])) {
  echo alert("Coming soon");
}

if (isset($_GET['s'])) {
  $query = "SELECT * FROM accounts WHERE 
              LOWER(`name`) LIKE LOWER(?) OR
              LOWER(`username`) LIKE LOWER(?) OR
              LOWER(`url`) LIKE LOWER(?) OR
              LOWER(`description`) LIKE LOWER(?) ORDER BY id DESC";
  $stmt = $sqlcon->prepare($query);
  $searchTerm = "%$_GET[s]%";
  $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
  $query = "SELECT * FROM accounts ORDER BY id DESC";
  $stmt = $sqlcon->prepare($query);
}
$stmt->execute();
$accounts = $stmt->get_result();
$stmt->close();
?>
<form action="" method="GET">
<div class="input-group mb-3">
<div class="input-group-prepend">
  <span class="input-group-text" id="basic-addon1"><?= icon("search") ?></span>
</div>
  <input type="text" name="s" class="form-control" placeholder="Search" autofocus>
</div>
</form>
<!-- 
  --------------------
  ADD NEW ENTRY MODAL
  --------------------
 -->
<div class="modal fade" id="addEntryModal" tabindex="-1" role="dialog" aria-labelledby="addEntryModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <!--<span aria-hidden="true">&times;</span>-->
        </button>
      </div>
      <div class="modal-body">
        <form action="" method="POST">
        <input type="hidden" name="add" value="1">
        <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Name</span>
        </div>
          <input type="text" class="form-control" name="name" placeholder="Name">
        </div>
        <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Username</span>
        </div>
          <input type="text" class="form-control" name="username" placeholder="Username">
        </div>
        <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Password</span>
        </div>
          <input type="password" class="form-control password" id="password" name="password" placeholder="Password" value="">
          <a href="javascript:void(0);" class="genPass btn btn-primary" data-output=".password">Generate</a>
        </div>
        <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">URL</span>
        </div>
          <input type="text" class="form-control"name="url"  placeholder="URL">
        </div>
        <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Description</span>
        </div>
          <textarea class="form-control" name="desc" placeholder="Description"></textarea>
        </div>

        <div class="input-group mb-3">

          <div class="input-group-prepend">
            <span class="input-group-text">2FA</span>
          </div>

          <input type="hidden" name="2fa" value="0">
          <div class="form-check form-switch">
            <input name="2fa" value="1" class="form-check-input tfa_switch" type="checkbox">
          </div>

        </div>

      <div class="input-group mb-3 tfa_dropdown">
        <input type="hidden" name="2fa_id" value="0">
        <span class="input-group-text">2FA Account</span>
        <?php echo $tfa_dropdown; ?>
      </div>
      
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <input type="submit" class="btn btn-primary" value="Create">
        </form>
      </div>
    </div>
  </div>
</div>


<!-- ACTION BUTTONS -->
<?php 
if (isset($_GET['s']) && !empty($_GET['s'])) {
  echo '
  <div class="row col-md-4 my-3">
    <a href="index.php" class="btn btn-primary">'.icon('x-circle').' Clear search</a>
  </div>
  ';
}
?>
<div class="row col-md-4 mb-3">
  <div class="col">
    <div class="btn-group">
      <a class="btn btn-primary" href="index.php"><?= icon('house-door-fill', color: "white") ?> Home</a>
      <a class="btn btn-secondary" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal"><?= icon('gear-fill', color: "white") ?> Settings</a>
      <a class="btn btn-danger" href="?lock=1"><?= icon('lock-fill', color: "white") ?> Lock</a>
    </div>
  </div>
</div>

<div class="row col-md-4">
  <div class="col">
    <div class="btn-group">
      <a class="btn btn-success" href="#" data-bs-toggle="modal" data-bs-target="#addEntryModal"><?= icon('plus-circle-fill', color: "white") ?> Add</a>
      <a class="btn btn-warning disabled" href="?reencrypt=1"><?= icon('key-fill', color: "white") ?> Re-encrypt</a>

      <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
      <?= icon("file-earmark-arrow-down-fill", color: "white") ?> Export to file
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="export.php?type=csv" target="_blank">CSV</a></li>
        <li><a class="dropdown-item" href="export.php?type=json" target="_blank">JSON</a></li>
        <li><a class="dropdown-item" href="export.php?type=sql" target="_blank">SQL</a></li>
      </ul>
    </div>
  </div>
</div>

<?php
echo "<hr>";
echo "<div id='errors'></div>"; # for outputting errors from javascript

if ($accounts->num_rows < 1) {
  die(alert("Nothing added yet!"));
}
$iteration = 1;
while ($account = $accounts->fetch_assoc()) {
  $checked = null;
  if ($account['2fa'] == 1) {
    $checked = "checked";
  }
  $iv = null;
  if (!empty($account['iv'])) {
    $iv = hex2bin($account['iv']);
  }
  # EDIT ENTRY
  $salt = null;
  if (!empty($account['salt'])) {
    $salt = $account['salt'];
  }
  $decryptedPass = decrypt($account['password'], $salt.ENCRYPTION_KEY, $iv);
  echo '
  <!-- Modal -->
  <div class="modal fade" id="editEntryModal'.$account['id'].'" tabindex="-1" role="dialog" aria-labelledby="editEntryModal'.$account['id'].'" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <!--<span aria-hidden="true">&times;</span>-->
          </button>
        </div>
        <div class="modal-body">
          <form action="" method="POST">
          <input type="hidden" name="edit" value="1">
          <input type="hidden" name="id" value="'.$account['id'].'">
          
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Name</span>
            </div>
            <input type="text" name="name" class="form-control" placeholder="Name" value="'.$account['name'].'">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Username</span>
            </div>
            <input type="text" name="username" class="form-control" placeholder="Username" value="'.$account['username'].'">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Password</span>
            </div>
            <input type="password" name="password" class="form-control password" placeholder="Password" value="'.$decryptedPass.'">
            <a href="javascript:void(0);" class="genPass btn btn-primary" data-output=".password">Generate</a>
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">URL</span>
            </div>
            <input type="text" name="url" class="form-control" placeholder="URL" value="'.$account['url'].'">
          </div>
          
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Description</span>
            </div>
            <textarea name="desc" class="form-control" placeholder="Description">'.$account['description'].'</textarea>
          </div>


          <div class="input-group mb-3">
          <input type="hidden" name="2fa" value="0">
            <div class="input-group">
              <span class="input-group-text">2FA</span>
              <div class="form-check form-switch">
                <input name="2fa" value="1" class="form-check-input tfa_switch" type="checkbox" '.$checked.'>
              </div>
            </div>
          </div>


          <div class="input-group mb-3 tfa_dropdown">
          <input type="hidden" name="2fa_id" value="0">
          <span class="input-group-text">2FA Account</span>';
            if (!empty($account['2fa_id'])) {
              echo getTFA_dropdown(selected: $account['2fa_id']);
              echo getTFA_otp(id: $account['2fa_id']);
            } else {
              echo $tfa_dropdown;
            }
            echo '
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <input type="submit" class="btn btn-primary" value="Save">
          </form>
        </div>
      </div>
    </div>
  </div>
    <!-- Modal -->
    <div class="modal fade" id="delEntryModal'.$account['id'].'" tabindex="-1" role="dialog" aria-labelledby="delEntryModal'.$account['id'].'" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete entry</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <!--<span aria-hidden="true">&times;</span>-->
            </button>
          </div>
          <div class="modal-body">
            <form action="" method="POST">
            <input type="hidden" name="del" value="1">
            <input type="hidden" name="id" value="'.$account['id'].'">
            <div class="alert alert-warning">Are you sure you want to delete entry '.$account['name'].'?</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <input type="submit" class="btn btn-danger" value="Delete">
            </form>
          </div>
        </div>
      </div>
    </div>
  ';
        if ($iteration == 1) {         
          echo "<table class='table table-hover table-dark' style='table-layout:fixed; word-break: break-word;'>
          <tr><th>Name</th><th>Username</th><th>Password</th><th>URL</th><th>Description</th><th>2FA</th></tr>";
        }
    $id = 0;
    echo "<tr id='account-".$account['id']."'>";
    for ($i=0;$i<6;$i++) {
    echo "<td>";
    if ($i == 3) {
        # URL Field
        
        echo "
        <a href='javascript:void(0);' onClick='copyTC(\"$account[id]$i$id\");'>".icon('clipboard-fill')."</a>
        <span id='$account[id]$i$id'><a href='$account[url]' target='_blank'>$account[url]</a></span>";
    } elseif ($i == 2) {
        # Password field
        echo "
        <a onClick='copyTC(\"$account[id]$i$id\");'>".icon('clipboard-fill')."</a>
        <a id='$account[id]$i$id-eye' onClick='reveal(\"$account[id]$i$id\");'>".icon('eye')."</a>
        <span id='$account[id]$i$id' style='display:none;font-size:11px;'>".$decryptedPass."</span>
        <span id='$account[id]$i$id-h' style='font-size:11px;'>****************</span>";
    } elseif ($i == 4) {
        # Description field, no need for copy
        echo $account["description"];
    } elseif ($i == 0) {
        # Name field, no need for copy
        echo "<a href='#' data-bs-toggle='modal' data-bs-target='#editEntryModal$account[id]'>".icon('pencil-square')."</a>
              <a href='#' data-bs-toggle='modal' data-bs-target='#delEntryModal$account[id]'>".icon('trash3-fill', color: 'red')."</a>
              ".$account["name"];
    } elseif ($i == 5) {
        # 2FA Field
        $tfa      = $account['2fa'];
        $tfa_id   = $account['2fa_id'];
        $tfa_link = icon("dash-circle", color: 'red');
        if ($tfa != "0") {
          $tfa_link = icon("check-circle", color: 'green');
          if (TFA_ENABLED == True && !empty($tfa_id)) {
            $tfa_link = "<a class='tfa_enabled' href='javascript:void(0);' data-tfaid='".$tfa_id."'>".icon('check-circle', color: 'green')."</a>";
          }
        }
        echo $tfa_link;
    } elseif ($i == 1) {
        # Not password field
        echo "<a onClick='copyTC(\"$account[id]$i$id\");'>".icon('clipboard-fill')."</a> <span id='$account[id]$i$id'>$account[username]</span>";
    }
    echo "</td>";
    }
    echo "</tr>";
    $id++;
    $iteration++;
}
echo "</table>";
?>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
  <div id="liveToast" class="toast bg-text-white bg-info hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <img src="img/ctc.png" class="rounded me-2" alt="Copied" style="width:30px;">
      <strong class="me-auto">Copied!</strong>
      <small>now</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      Copied to clipboard!
    </div>
  </div>
</div>
</body>