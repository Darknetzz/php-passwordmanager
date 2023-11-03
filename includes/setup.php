<?php require_once("includes/bootstrap.php"); ?>
<body>
<div class="container" style="margin-top:10px;">
<?php


$status = 0;
$error  = [];
$info   = [];

# Check that this file is included, so we have our constants from index.php
if (strpos($_SERVER['SCRIPT_NAME'], "index.php") === false) {
  die(alert("You must run the setup from index.php, not ".$_SERVER['SCRIPT_NAME']));
}

# Confirm delete
if ($_GET['reset'] == 1) {
  unlink(CONFIG_FILE);
  echo alert("Configuration file deleted.", "success");
}

# Check presence of configuration file
if (file_exists(CONFIG_FILE) && !empty(file_get_contents(CONFIG_FILE))) {
  echo alert("The config file ".CONFIG_FILE." already exists and is not empty. If you proceed the file will be deleted!", "danger");
  echo "<a href='?reset=1' class='btn btn-danger'>I understand, delete the current configuration.</a>";
  die();
}

# Touch the file
touch(CONFIG_FILE);

/* ────────────────────────────────────────────────────────────────────────── */
/*                              Custom functions                              */
/* ────────────────────────────────────────────────────────────────────────── */
$inputs = "";
function addCategory(string $categoryName) {
  global $inputs;
  $inputs .= '
  <tr><th colspan="100%"><h4>'.$categoryName.'</h4></th></tr>
  ';
}
function addInput(string $name, array $opts = []) {
  global $inputs;

  $defaultopts = [
    'class'       => 'form-control',
    'value'       => '',
    'placeholder' => '',
    'genpass'     => false,
    'type'        => 'text',
    'description' => '',
    'attributes'  => '',
  ];

  foreach ($defaultopts as $optname => $optval) {
    if (empty($opts[$optname])) {
      $opts[$optname] = $optval;
    }
  }

  $class        = $opts['class'];
  $placeholder  = (!empty($opts['placeholder']) ? $opts['placeholder'] : $opts['value']);

  $value = $opts['value'];
  if (!empty($_POST[$name])) {
    $value = $_POST[$name];
  } 

  $inputs .= '
    <tr>
      <td>'.$opts['description'].'</td> 
      <td>
        <input type="hidden" name="'.$name.'_DEFAULT" value="'.$opts['placeholder'].'">
        <input 
          type="'.$opts['type'].'"
          id="'.$name.'"
          name="'.$name.'"
          placeholder="'.$placeholder.'"
          class="'.$opts['class'].'"
          value="'.$value.'"
          '.$opts['attributes'].'
        >
      </td>
    </tr>';
}

$inputs = "";
  addCategory("MySQL");

  addInput('MYSQL_HOST', 
  [
    'placeholder' => '127.0.0.1',
    'description' => 'MySQL Host', 
  ]);

  addInput('MYSQL_USER', 
  [
    'placeholder' => 'root', 
    'description' => 'MySQL Username'
  ]);

  addInput('MYSQL_PASSWORD', 
  [
    'description' => 'MySQL Password',
    'type'        => 'password'
  ]);

  addInput('MYSQL_DB', 
  [
    'placeholder' => 'php_passwordmanager',
    'description' => 'MYSQL Database',
  ]);

  addCategory("General");

  addInput('MASTER_PASSWORD', 
  [
    'description' => 'Vault Master Password',
    'type'        => 'password',
    'attributes'  => 'required',
  ]);

  addInput('ENC_METHOD', 
  [
    'placeholder' => 'aes-256-cbc',
    'description' => 'Encryption Method',
    'attributes'  => 'readonly'
  ]);

  addInput('SALT', 
  [
    'placeholder'     => passGen(32),
    'description' => 'Salt',
  ]);

  addInput('TITLE', 
  [
    'placeholder'     => 'PHP Password Manager',
    'description' => 'Page Title',
  ]);

  addInput('BACKGROUND_COLOR', 
  [
    'class'       => 'form-control form-control-color',
    'description' => "Background Color",
    'value'       => $bgcolor,
    'type'        => 'color',
  ]);

  addInput('COLOR', 
  [
    'class'       => 'form-control form-control-color',
    'description' => "Font Color",
    'value'       => $color,
    'type'        => 'color',
  ]);

/* ────────────────────────────────────────────────────────────────────────── */
/*                                 Config card                                */
/* ────────────────────────────────────────────────────────────────────────── */
$configCard = '
<div class="card bg-dark">
<h3 class="card-header">Configuration</h3>
<div class="card-body">
'.alert('Please specify your configuration here, or alternatively change the <code>config_example.php</code> to your likings and rename it to <code>config.php</code>.').'
<hr>
<form action="" method="POST">
<table class="table table-default table-dark">
'.$inputs.'
</table>
<button class="btn btn-success">Save</button>
</form>
</div>
</div>
</div>
';

if (!empty($_POST)) {

while ($status == 0) {
  /* ────────────────────────────────────────────────────────────────────────── */
  /*                                SETUP STARTS                                */
  /* ────────────────────────────────────────────────────────────────────────── */
  setup_info("Running setup...");

  /* ────────────────────────────────────────────────────────────────────────── */
  /*                       Check if config already exists                       */
  /* ────────────────────────────────────────────────────────────────────────── */
  setup_info("Checking for presence of config file...");
  if (file_exists(CONFIG_FILE)) {
      if (!empty(file_get_contents(CONFIG_FILE))) {
          setup_error("The file ".CONFIG_FILE." already exists and is non-empty.", 1);
      }
      setup_info("The file ".CONFIG_FILE." exists, but is empty. Deleting file...", "warning");
      unlink(CONFIG_FILE);
  }

  /* ────────────────────────────────────────────────────────────────────────── */
  /*                              Check if writable                             */
  /* ────────────────────────────────────────────────────────────────────────── */
  // $f = fopen(CONFIG_FILE, 'w+');
  // if (!$f) {
  //   setup_error('The configuration file <code>'.CONFIG_FILE.'</code> doesn\'t exist, and this script does not have access to it.
  //   Please change your configuration in <code>config_example.php</code> and rename/copy the file to <code>config.php</code>', 2);
  //   break;
  // }

  /* ────────────────────────────────────────────────────────────────────────── */
  /*                         Verify all required values                         */
  /* ────────────────────────────────────────────────────────────────────────── */
  // foreach ($values as $c => $value) {
  //   foreach ($value as $thisval) {
  //     $name = $thisval[0];
  //     $default = $thisval[1];
  //     $description = $thisval[2];
      
  //     $setup[$name] = $_POST[$name];
  //     if (empty($setup[$name])) {
  //       $setup[$name] = $default;
  //     }
  //   }
  // }
  $setup = $_POST;

  foreach ($setup as $var => $val) {

    if (strpos($var, '_DEFAULT') !== false) {
      continue;
    }

    if (empty($val)) {
      $setup[$var] = $_POST[$var.'_DEFAULT'];
    }

  }

  
  if (empty($setup['MASTER_PASSWORD'])) {
    setup_error("You must specify a master password!", 40);
    break;
  }

  if (strlen($setup['MASTER_PASSWORD']) < MASTER_PASSWORD_MINLEN) {
    setup_error("The master password must be more than ".MASTER_PASSWORD_MINLEN." characters.", 41);
    break;
  }

    /* ────────────────────────────────────────────────────────────────────────── */
    /*                              Connect to MySQL                              */
    /* ────────────────────────────────────────────────────────────────────────── */
    try {
        $sqlcon = new mysqli($setup['MYSQL_HOST'], $setup['MYSQL_USER'], $setup['MYSQL_PASSWORD']);
    } catch (Throwable $t) {
        setup_error("Unable to connect to MySQL host $setup[MYSQL_HOST]", 4);
        setup_error($t->getMessage());
        break;
    }

    /* ────────────────────────────────────────────────────────────────────────── */
    /*                               Create database                              */
    /* ────────────────────────────────────────────────────────────────────────── */
    setup_info("Creating database $setup[MYSQL_DB] @ $setup[MYSQL_HOST]...");
    try {
        $dbName = $setup['MYSQL_DB'];
        mysqli_query($sqlcon, "CREATE DATABASE $dbName;"); # TODO: directly allowing a POST value in the query...
        setup_info("Database $dbName created!", "success");
    } catch (Throwable $t) {
        // $attempts = 0;
        // $try      = 3;
        // $created  = 0;
        // do {
        //     try {
        //         setup_info("The database $dbName could not be created, attempting to rename...", "warning");
        //         $append = passGen(5, 'lud');

        //         # Sjekk om databasen finnes
        //         $dbName = $dbName."_".$append;
        //         $setup['MYSQL_DB'] = $dbName;

        //         mysqli_query($sqlcon, "DROP DATABASE IF EXISTS $dbName;");
        //         mysqli_query($sqlcon, "CREATE DATABASE $dbName;");         
        //         setup_info("Database $dbName created!", "success");
        //         $created = 1;
        //     } catch (Throwable $t) {
        //         $attempts++;
        //         setup_info("Unable to create database $dbName.", "warning");
        //         setup_info($t, "danger");
        //     }

        //     break;

        // } while ($attempts < $try);

        if ($created == 0) {
            setup_error("Database could not be created. Exiting...");
            setup_error($t->getMessage());
        }
    }

    /* ────────────────────────────────────────────────────────────────────────── */
    /*                               Select database                              */
    /* ────────────────────────────────────────────────────────────────────────── */
    try {
      $selectdb = mysqli_select_db($sqlcon, $dbName);
    } catch (Throwable $t) {
      setup_error("Unable to select database $dbName", 5);
      setup_error($t->getMessage());
      break;
    }

    /* ────────────────────────────────────────────────────────────────────────── */
    /*                           Create tables                                    */
    /* ────────────────────────────────────────────────────────────────────────── */
    try {
      include_once('sql_template.php');

      $thisTable = "";
      foreach ($sql_template as $tableName => $createColumn) {
        if ($tableName != $thisTable) {
          $query = "CREATE TABLE $tableName (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id));";
          $query = $sqlcon->prepare($query);
          $query->execute();
          $thisTable = $tableName;
        }

        foreach ($createColumn as $col) {
          $colName = $col[0];
          $colType = $col[1];
          $query   = "ALTER TABLE `$thisTable` ADD COLUMN `$colName` $colType";
          $query   = $sqlcon->prepare($query);
          $query->execute();
        }
      }
        // $createTable = '
        // CREATE TABLE `accounts` (
        //     `id` int NOT NULL,
        //     `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        //     `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        //     `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        //     `salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
        //     `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        //     `description` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        //     `2fa` tinyint(1) NOT NULL DEFAULT "0"
        //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_bin;
        // ';
        // $createTable = $sqlcon->prepare($createTable);
        // $createTable->execute();
    } catch (Throwable $t) {
        setup_error("Unable to create tables for database $setup[MYSQL_DB]", 6);
        setup_error($t->getMessage());
        break;
    }


    /* ────────────────────────────────────────────────────────────────────────── */
    /*                              Write config file                             */
    /* ────────────────────────────────────────────────────────────────────────── */
    try {
        // $iv_len   = openssl_cipher_iv_length($setup['ENC_METHOD']);
        // $iv_bytes = openssl_random_pseudo_bytes($iv_len);
        // $iv       = bin2hex($bytes);
      
      # I know this does nothing, but at least the password can't be seen in cleartext
        $encodedPass = base64_encode($setup['MYSQL_PASSWORD']);
        $configToWrite = '
<?php
# Change this file to match your SQL-connection and add a master password, or configure it from the setup.php.

/* ────────────────────────────────────────────────────────────────────────── */
/*                               Master password                              */
/* ────────────────────────────────────────────────────────────────────────── */
/*

    How:
        - Use a hashing tool online like https://roste.org/rand/#hash
        - Insert the pepper + master password + salt into the SHA512 input field.

    Example:
        If salt is set to SALT, your password needs to be hashed like this: <YOUR_PASSWORD>SALT

*/

# Optional appended salt
define("SALT", "'.$setup['SALT'].'");

# Your master password in SHA512 format
# This password is set to be CHANGEME, with the above salt.
define("MASTER_PASSWORD", "'.hash('sha512', $setup['MASTER_PASSWORD'].$setup['SALT']).'");

# The encryption method to use
define("ENC_METHOD", "aes-256-cbc");

if (!in_array(ENC_METHOD, openssl_get_cipher_methods())) {
    die("Invalid cipher method ".ENC_METHOD);
}

/* ────────────────────────────────────────────────────────────────────────── */
/*                         MySQL Connection Parameters                        */
/* ────────────────────────────────────────────────────────────────────────── */
define("MYSQL_HOST", "'.$setup['MYSQL_HOST'].'");
define("MYSQL_USER", "'.$setup['MYSQL_USER'].'");
define("MYSQL_PASSWORD", base64_decode("'.$encodedPass.'"));
define("MYSQL_DB", "'.$setup['MYSQL_DB'].'");

$sqlcon = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);

/* ────────────────────────────────────────────────────────────────────────── */
/*                                    Other                                   */
/* ────────────────────────────────────────────────────────────────────────── */
define("BACKGROUND_COLOR", "'.$setup['BACKGROUND_COLOR'].'");
define("COLOR", "'.$setup['COLOR'].'");
?>
      ';

      // fwrite($f, $configToWrite);
      file_put_contents(CONFIG_FILE, $configToWrite);
      echo "<div class='alert alert-success'>Config updated! <a href=''>Go to login</a></div>";
      die();
      } catch (Throwable $t) {
        setup_error("Unable to create config file");
        setup_error($t->getMessage());
        break;
      }
} 


/* ────────────────────────────── INFO MESSAGES ───────────────────────────── */
foreach ($info as $i) {
    echo $i;
}

/* ──────────────────────────────── STATUS OK ─────────────────────────────── */
if ($status == 0) {

    if (!file_get_contents(CONFIG_FILE) || empty(file_get_contents(CONFIG_FILE))) {
      setup_error("Database was created but ".CONFIG_FILE." is empty. Please verify permissions.");
    }

    echo alert("Setup complete! <a href=''>Log in?</a>", "success");
/* ────────────────────────────── ERROR OCCURED ───────────────────────────── */
} else {
    echo alert("<b>Error $status occured:</b> <hr>".implode("<br><br>", $error), "danger");

    if (file_exists(CONFIG_FILE)) {
        unlink(CONFIG_FILE);
    }

    // foreach ($error as $e) {
    //     echo alert($e, "danger");
    // }
}
echo "<hr>";
}

echo $configCard;

?>
</div>
</body>