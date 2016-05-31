<?php


/* Local */

if ($_SERVER['HTTP_HOST'] == 'dev.tafhimui.com') {
    /* Server */
    $server = 'localhost'; // this may be an ip address instead
    $user = 'tafhimui_nutact';
    $pass = 'am1Keona!?#';
    $database = 'tafhimui_nutact';
} else if($_SERVER['HTTP_HOST'] == 'localhost') {

    $server = 'localhost'; // this may be an ip address instead
    $user = 'root';
    $pass = '';
    $database = 'nutshellacton';
    $port = '';

} else {

    $server = 'localhost'; // this may be an ip address instead
    $user = 'root';
    $pass = '';
    $database = 'nutshellacton';
    $port = '';
}

define('DB_HOST', $server);
define('DB_PORT', $port);
define('DB_USER', $user);
define('DB_PASS', $pass);
define('DB_NAME', $database);


//define('DB_HOST', getenv('OPENxcSHIFT_MYSQL_DB_HOST'));
//define('DB_PORT', getenv('OPENSHIFT_MYSQL_DB_PORT'));
//define('DB_USER', getenv('OPENSHIFT_MYSQL_DB_USERNAME'));
//define('DB_PASS', getenv('OPENSHIFT_MYSQL_DB_PASSWORD'));
//define('DB_NAME', getenv('OPENSHIFT_GEAR_NAME'));


/*$server = DB_HOST; // this may be an ip address instead
$user = DB_USER;
$pass = DB_PASS;
$database = DB_NAME;
$port = DB_PORT;*/