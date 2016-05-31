<?php
/**
 * Created by PhpStorm.
 * User: ahsuoy
 * Date: 4/10/2016
 * Time: 3:45 PM
 */

$slim_settings = function ($host) {
    $app_settings_list = [
        'dev.tafhimui.com' => [
            'settings' => [
                'displayErrorDetails' => true,

                // Monolog settings
                'logger' => [
                    'name' => 'slim-app',
                    'path' => __DIR__ . '/logs/app.log',
                ],
            ]
        ],
        '127.0.0.1' => [
            'settings' => [
                'displayErrorDetails' => true,

                // Monolog settings
                'logger' => [
                    'name' => 'slim-app',
                    'path' => __DIR__ . '/logs/app.log',
                ],
            ],
        ],
        'localhost' => [
            'settings' => [
                'displayErrorDetails' => true,

                // Monolog settings
                'logger' => [
                    'name' => 'slim-app',
                    'path' => __DIR__ . '/logs/app.log',
                ],
            ],
        ]
    ];

    if (isset($app_settings_list[$host])) return $app_settings_list[$host];
    else return null;
};

$nutshell_api = function($targetAccount) {

    $nutApi = array(
        'client' => "b774443e2ae5a6a5bd3fbe9597b3730236afcee1",
        'dev'    => "3bd97fd464d4b89a3f9639c381fcfa7198af65be"
    );

    //$nutApi = "b774443e2ae5a6a5bd3fbe9597b3730236afcee1";
    //$nutApi = "3bd97fd464d4b89a3f9639c381fcfa7198af65be";
    return $nutApi[$targetAccount];
};

$nutshell_user = function($targetAccount) {
    //$nutUser = "thom@instructiontech.net";
    $nutUser = array(
        'client' => "thom@instructiontech.net",
        'dev'    => "support@iteratemarketing.com"
    );
    /*$nutUser = "youshafarokey@theportlandcompany.com";*/
    return $nutUser[$targetAccount];
};

$acton_account = function($targetAccount) {


    $actonAccount = array(

        'client' => array(

            'grant_type'    => 'password',
            'username'      => 'thom@instructiontech.net',
            'password'      => 'goinggone',
            'client_id'     => 'kIXxGopuFbgGTh6u1RAH2gQhP24a',
            'client_secret' => 'SEezLzMVsUcrpuwUaVEbpJP6TKca'

        ),
        'dev'    => array(

            'grant_type'    => 'password',
            'username'      => 'support@iteratemarketing.com',
            'password'      => 'Jtvnxj7nhq5s ',
            'client_id'     => 'kIXxGopuFbgGTh6u1RAH2gQhP24a',
            'client_secret' => 'SEezLzMVsUcrpuwUaVEbpJP6TKca'

        )
    );

    return $actonAccount[$targetAccount];

};

$acton_user = function($targetAccount) {

    $actonUser = array(

        'client'  => 'thom@instructiontech.net',
        'dev'     => 'support@iteratemarketing.com'

    );

    return $actonUser[$targetAccount];

};

$acton_password = function($targetAccount) {

    $actonPassword = array(

        'client'  => 'goinggone',
        'dev'     => 'welcome'

    );

    return $actonPassword[$targetAccount];

};


$acton_id = function($targetAccount) {

    $actonId = array(

        'client'  => 'kIXxGopuFbgGTh6u1RAH2gQhP24a',
        'dev'     => 'kIXxGopuFbgGTh6u1RAH2gQhP24a'

    );

    return $actonId[$targetAccount];

};

$acton_secret = function($targetAccount) {

    $actonSecret = array(

        'client'  => 'SEezLzMVsUcrpuwUaVEbpJP6TKca',
        'dev'     => 'SEezLzMVsUcrpuwUaVEbpJP6TKca'

    );

    return $actonSecret[$targetAccount];

};