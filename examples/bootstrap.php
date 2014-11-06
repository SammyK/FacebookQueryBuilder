<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SammyK\FacebookQueryBuilder\FQB;

if ( ! file_exists(__DIR__ . '/config.php'))
{
    die('You need to copy /examples/config.php.dist to /examples/config.php and enter your app credentials to run the examples.');
}

// Pull in config
$config = require __DIR__ . '/config.php';

// Load up!
$fqb = new FQB([
    'app_id' => $config['app_id'],
    'app_secret' => $config['app_secret'],
    'default_access_token' => $config['access_token'],
]);
