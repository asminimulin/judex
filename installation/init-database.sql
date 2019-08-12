CREATE USER IF NOT EXISTS `judex`
    IDENTIFIED BY "password";
CREATE DATABASE IF NOT EXISTS `judex`
	CHARACTER SET utf8
	COLLATE utf8_general_ci;
GRANT ALL ON `judex`.*
	TO "judex"@"localhost"
	IDENTIFIED BY "password";
GRANT ALL ON `judex`.*
	TO "judex"@"localhost"
	IDENTIFIED BY "password";

