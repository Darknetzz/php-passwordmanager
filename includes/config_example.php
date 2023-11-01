<?php
# Change this file to match your SQL-connection and add a master password, or configure it from the setup.php.

/* ────────────────────────────────────────────────────────────────────────── */
/*                               Master password                              */
/* ────────────────────────────────────────────────────────────────────────── */
/*

    How:
        - Use a hashing tool online like https://roste.org/rand/#hash
        - Insert the master password + salt into the SHA512 input field.

    Example:
        If salt is set to SALT, your password needs to be hashed like this: <YOUR_PASSWORD>123

*/

# Optional appended salt
define("SALT", "CHANGEME");

# Your master password in SHA512 format
# Generate a hash using the salt above and your password
# https://roste.org/rand/#hash
# Current password: CHANGEME
define("MASTER_PASSWORD", "90eedcbe58aacedc7dfa2ce8311f9cc6e92481e9ff2aadd43a98d806576effc8663a51588fd713098c79a6a7082aa485774742069437cb5e61c61c9a2624a79a");

# The encryption method to use
define("ENC_METHOD", "AES-256-CBC");

# The IV to use for the master password
// define("MASTER_IV", "63325357416f6e357474616f53787651");

/* ────────────────────────────────────────────────────────────────────────── */
/*                         MySQL Connection Parameters                        */
/* ────────────────────────────────────────────────────────────────────────── */
define("MYSQL_HOST", "localhost");
define("MYSQL_USER", "root");
define("MYSQL_PASSWORD", "");
define("MYSQL_DB", "php_passwordmanager");

$sqlcon = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);

/* ────────────────────────────────────────────────────────────────────────── */
/*                                    Other                                   */
/* ────────────────────────────────────────────────────────────────────────── */
define("BACKGROUND_COLOR", "#111");
?>