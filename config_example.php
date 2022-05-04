<?php
# Change this file to match your SQL-connection and add a master password.

# Your master password in SHA512 format (current password: CHANGEME)
$masterPassword = "101289c2f34b5dea17245e030720cd2a7c6be2307147ff188b532170bc0f16a05b1cc694be6826e516e0496105b8b8a681d908dd6db2d5d71a5ff281c4967acc";

$host = "localhost";
$user = "root";
$pass = "";
$db = "passwords";
$sqlcon = mysqli_connect($host, $user, $pass, $db);
?>