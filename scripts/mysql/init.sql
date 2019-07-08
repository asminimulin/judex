CREATE USER IF NOT EXISTS `judex-master`
    IDENTIFIED BY "password";
  CREATE DATABASE IF NOT EXISTS judex
    CHARACTER SET utf8
    COLLATE utf8_general_ci;
  GRANT ALL ON `judex`.*
    TO "judex-master"@"localhost"
    IDENTIFIED BY "password";
  GRANT ALL ON `judex`.*
    TO "judex-master"@"localhost"
    IDENTIFIED BY "password";

