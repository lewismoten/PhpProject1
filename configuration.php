<?php 

// Database connection information.
define("DB_HOST", "127.0.0.1");
define("DB_PORT", "3306");
define("DB_SOCKET", "");
define("DB_NAME", "test_database");
define("DB_USER", "test_username");
define("DB_PASSWORD", "test_password");

// Encryption values used to store and retrieve sensitive information
define("ENCRYPTION_CIPHER", MCRYPT_RIJNDAEL_256);
define("ENCRYPTION_MODE", MCRYPT_MODE_ECB);
define("ENCRYPTION_KEY", 0xbad1bad1bad1bad1bad1bad1bad1bad1bad1bad1bad1bad1bad1bad1bad1bad1);

// Number of times to rehash authentication credentials
define("KEY_STRENGTH", 65536);

?>
