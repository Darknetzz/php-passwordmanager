<?php
# Change this file to match your SQL-connection and add a master password, or configure it from the setup.php.

/* ────────────────────────────────────────────────────────────────────────── */
/*                               Master password                              */
/* ────────────────────────────────────────────────────────────────────────── */
# Your master password in SHA512 format (current password: CHANGEME)
define("MASTER_PASSWORD", "101289c2f34b5dea17245e030720cd2a7c6be2307147ff188b532170bc0f16a05b1cc694be6826e516e0496105b8b8a681d908dd6db2d5d71a5ff281c4967acc");

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