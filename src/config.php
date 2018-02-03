<?php
define('REGISTER_STATUS', TRUE);

//define('DB_HOST', '203.70.112.206');
define('DB_HOST', 'v4.srv.seadog007.me');
define('DB_NAME', 'fish');
define('DB_USER', 'fish');
define('DB_PASS', 'fish');
define('DB_TIMEZONE', 'Asia/Taipei');

define('NEXMO_KEY', '063f076c');
define('NEXMO_SECRET', 'cd558e06c9c7f199');
define('SMS_CODE_EXPIRE_TIME', 1800);

$connection_string = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
$db = new PDO($connection_string, DB_USER, DB_PASS);
