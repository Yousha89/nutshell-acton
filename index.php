<?php
/**
 * Created by PhpStorm.
 * User: ahsuoy
 * Date: 4/10/2016
 * Time: 3:35 PM
 *
 * Nutshell and Act-on synchronization
 *
 *
 */

session_start();


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once 'vendor/autoload.php';
require_once 'vendor/dbe/class.dbe.inc';
require_once 'vendor/nutshell-api/NutshellApi.php';
require_once 'vendor/nutshell-api/NutshellApiException.php';
require_once 'config/app.php';
require_once 'config/database.php';
require_once 'vendor/mashape/unirest-php/src/Unirest.php';
require_once 'vendor/mashape/unirest-php/acton/ActOnAccount.php';
require_once 'vendor/mashape/unirest-php/acton/initAccess.php';

/* Define site domain */

define('SITE_DOMAIN', 'http://' . $_SERVER['HTTP_HOST'] . rtrim($_SERVER['PHP_SELF'], 'index.php'));

$app = new Slim\App($slim_settings($_SERVER['HTTP_HOST']));

$nutshellApiDev = new NutshellApi($nutshell_user("dev"), $nutshell_api("dev"));

$nutshellApiClient = new NutshellApi($nutshell_user("client"), $nutshell_api("client"));

$actOnAccountClient = new \acton\ActOnAccount($acton_account('client'), 'client');


$actOnAccountDev = new \acton\ActOnAccount($acton_account('dev'), 'dev');

$app->actOnAccountClient = $actOnAccountClient;
$app->actOnAccountDev = $actOnAccountDev;

$app->nutshellApiDev = $nutshellApiDev;
$app->nutshellApiClient = $nutshellApiClient;

$app->currentActOnAccount = '';

function actOnAuth ($headers = array(), $body = array(), $currentActOnAccount = 'dev') {

    //print_r($body);
    $body = Unirest\Request\Body::Form($body);

    $response = Unirest\Request::post('https://restapi.actonsoftware.com/token', $headers, $body);

    $response = json_decode(json_encode($response), true);

    $oAuth = array();

    foreach($response['body'] as $key => $val) {

        $oAuth[$key] = $val;

    }

    $oAuth['current_time'] = time();

    $_SESSION['oAuth'][$currentActOnAccount] = $oAuth;

    //print_r($_SESSION);

};

function actOnRefreshToken ($body = array(), $currentActOnAccount = 'dev') {

    $header = array('Accept'=> 'application/json');
    $refreshData = array(

        'refresh_token'   => $_SESSION['oAuth'][$currentActOnAccount]['refresh_token'],
        'grant_type'      => 'refresh_token',
        'client_id'       => $body['client_id'],
        'client_secret'   => $body['client_secret']

    );

    actOnAuth($header, $refreshData, $currentActOnAccount);


}

$app->checkAccessExpires = function($body = array(), $currentActOnAccount = 'dev') {

    $header = array('Accept'=> 'application/json');

    if(!isset($_SESSION['oAuth'][$currentActOnAccount]['access_token'])) {


        actOnAuth($header, $body, $currentActOnAccount);

    }else {

        if((time() - $_SESSION['oAuth'][$currentActOnAccount]['current_time']) > ($_SESSION['oAuth'][$currentActOnAccount]['expires_in'] - 20)) {

            actOnRefreshToken($body, $currentActOnAccount);

        }

    }

    return $_SESSION['oAuth'][$currentActOnAccount]['access_token'];

};

$app_initAccess = new \acton\initAccess();
$app->initAccess = $app_initAccess;

$app_db = new \DBE\DBE();
$app_db->DBESetup(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
$app_db->connect();
$app->db = $app_db;

require_once 'src/routes.php';

$app->run();