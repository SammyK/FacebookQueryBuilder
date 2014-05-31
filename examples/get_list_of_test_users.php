<?php

require_once __DIR__ . '/bootstrap.php';

use SammyK\FacebookQueryBuilder\FQB;
use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

/**
 * Gets a list of test users for an app.
 * Requires an app access token.
 */

// Make an app access token
FQB::setAccessToken($config['app_id'] . '|' . $config['app_secret']);

try
{
    $users = $fqb->object($config['app_id'] . '/accounts/test-users')->fields('id','login_url')->limit(10)->get();
}
catch (FacebookQueryBuilderException $e)
{
    echo '<p>Error: ' . $e->getMessage() . "\n\n";
    echo '<p>Facebook SDK Said: ' . $e->getPrevious()->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

if (count($users) > 0)
{
    echo '<h1>Test Users For App ID ' .  $config['app_id']  . '</h1>' . "\n\n";
    foreach ($users as $user)
    {
        var_dump($user->toArray());
    }
}
else
{
    echo 'No test users returned for app ID ' . $config['app_id'] . "\n\n";
}
