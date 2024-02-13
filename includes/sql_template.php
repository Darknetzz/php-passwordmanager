<?php
  $sql_template["accounts"] = [
    ["name", "varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;"],
    ["username", "varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;"],
    ["password", "varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;"],
    ["salt", "varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;"],
    ["iv", "varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;"],
    ["url", "varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;"],
    ["description", "text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;"],
    ["2fa", "tinyint(1) NOT NULL DEFAULT 0;"],
    ["2fa_id", "varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT;"],
  ];
//   'ALTER TABLE `accounts` ADD COLUMN `id` int NOT NULL;',
//   'ALTER TABLE `accounts` ADD COLUMN `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;',
//   'ALTER TABLE `accounts` ADD COLUMN `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;',
//   'ALTER TABLE `accounts` ADD COLUMN `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;',
//   'ALTER TABLE `accounts` ADD COLUMN `salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;',
//   'ALTER TABLE `accounts` ADD COLUMN `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;',
//   'ALTER TABLE `accounts` ADD COLUMN `description` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;',
//   'ALTER TABLE `accounts` ADD COLUMN `2fa` tinyint(1) NOT NULL DEFAULT \'0\';',
?>