<?php
# Check presence of configuration file
if (!file_exists('config.php')) {
    
# Constants and defaults
if (!defined("MASTER_PASSWORD")) {
    define("MASTER_PASSWORD", "");
}

  echo '<div class="container" style="max-width:60%">';

  $f = fopen('config.php', 'w');
  if (!$f) {
    die('<div class="alert alert-danger">❌ The configuration file <code>config.php</code> doesn\'t exist, and this script does not have access to it.
    Please change your configuration in <code>config_example.php</code> and rename the file to <code>config.php</code></div>');
  } else {
    echo '<div class="alert alert-warning">⚠️ The configuration file <code>config.php</code> doesn\'t exist.<br>
    Please modify the file <code>config_example.php</code> and rename it to <code>config.php</code> or specify your configuration below.</div>';
  }
  fclose($f);
  unlink('config.php');

  $category = function($name) {
    return '<tr><th colspan=100%"><h4>'.$name.'</h4></th></tr>';
  };
  $option = function($name, $type, $default = "") {
    return '<tr><td>'.$name.'</td> <td>
    <input type="hidden" name="'.$name.'" value="'.$default.'">
    <input type="'.$type.'" name="'.$name.'" placeholder="'.$default.'" class="form-control"></td></tr>';
  };

  $configCard = '
  <div class="card">
  <h3 class="card-header">Configuration</h3>
  <div class="card-body">
  <form action="" method="POST" style="max-width:60%;">
  <table class="table table-default">
  '.$category("Master password").'
  '.$option("MASTER_PASSWORD", "password", "").'

  '.$category("MySQL").'
  '.$option("MYSQL_HOST", "text", "localhost").'
  '.$option("MYSQL_USER", "text", "root").'
  '.$option("MYSQL_PASSWORD", "password", "").'
  '.$option("MYSQL_DB", "text", "php_passwordmanager").'

  '.$category("Other").'
  '.$option("BACKGROUND_COLOR", "color", "#111").'

  </table>
  <button class="btn btn-success">Save</button>
  </form>
  </div>
  </div>
  </div>
  ';

  if (!empty($_POST)) {
    print_r($_POST);
    foreach ($_POST as $var => $val) {
      if (empty($_POST[$var]) && $var != "MYSQL_PASSWORD") {
        die("<div class='alert alert-danger'>ERROR: You must specify $var!</div>".$configCard);
      }
    }

    try {
        $sqlcon = new mysqli($_POST['MYSQL_HOST'], $_POST['MYSQL_USER'], $_POST['MYSQL_PASSWORD']);
        if (!$sqlcon) {
            echo "<div class='alert alert-danger'>Unable to connect to database at $_POST[MYSQL_HOST]</div>";
            die($configCard);
        }

        try {
            $sql = "CREATE DATABASE $_POST[MYSQL_DB]";
            $sql = $sqlcon->prepare($sql);
            $sql->execute();
        } catch (Throwable $t) {
            # Silently assume the database already exists
            // echo "<div class='alert alert-danger'>Unable to create database $_POST[MYSQL_DB]: $t</div>";
        }

        try {
            $selectdb = mysqli_select_db($sqlcon, $_POST['MYSQL_DB']);
        } catch (Throwable $t) {
            echo "<div class='alert alert-danger'>Unable to select database $_POST[MYSQL_DB]: $t</div>";
            die($configCard);
        }

        try {
            $createTable = '
            CREATE TABLE `accounts` (
                `id` int NOT NULL,
                `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                `salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                `description` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                `2fa` tinyint(1) NOT NULL DEFAULT "0"
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_bin;
            ';
            $createTable = $sqlcon->prepare($createTable);
            $createTable->execute();
        } catch (Throwable $t) {
            echo "<div class='alert alert-danger'>Unable to create table $_POST[MYSQL_DB]: $t</div>";
            die($configCard);
        }

        mysqli_connect($_POST['MYSQL_HOST'] ,$_POST['MYSQL_USER'] ,$_POST['MYSQL_PASSWORD'] ,$_POST['MYSQL_DB'] );
    } catch (Throwable $t) {
        $sqlerr = true;
        echo "<div class='alert alert-danger'>Unable to connect to database at $_POST[MYSQL_HOST]: $t</div>";
    }

      if (!$sqlerr) {
        # I know this does nothing, but at least the password can't be seen in cleartext
        $encodedPass = base64_encode($_POST['MYSQL_PASSWORD']);
        $configToWrite = '
<?php
# Change this file to match your SQL-connection and add a master password.

/* ────────────────────────────────────────────────────────────────────────── */
/*                               Master password                              */
/* ────────────────────────────────────────────────────────────────────────── */
# Your master password in SHA512 format
define("MASTER_PASSWORD", "'.hash('sha512', $_POST['MASTER_PASSWORD']).'");

/* ────────────────────────────────────────────────────────────────────────── */
/*                         MySQL Connection Parameters                        */
/* ────────────────────────────────────────────────────────────────────────── */
define("MYSQL_HOST", "'.$_POST['MYSQL_HOST'].'");
define("MYSQL_USER", "'.$_POST['MYSQL_USER'].'");
define("MYSQL_PASSWORD", base64_decode("'.$encodedPass.'"));
define("MYSQL_DB", "'.$_POST['MYSQL_DB'].'");

$sqlcon = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);

/* ────────────────────────────────────────────────────────────────────────── */
/*                                    Other                                   */
/* ────────────────────────────────────────────────────────────────────────── */
define("BACKGROUND_COLOR", "#111");
?>
      ';
      $f = fopen('config.php', 'w');
      fwrite($f, $configToWrite);
      echo "<div class='alert alert-success'>Config updated! <a href=''>Go to login</a></div>";
      die();
      }
    }
  
  echo '<div class="alert alert-primary">✔️ File <code>config.php</code> is writable!</div>';
  unlink('config.php');

  echo $configCard;
  die();
}
?>