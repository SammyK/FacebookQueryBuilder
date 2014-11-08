<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Gets a list of test users for an app.
 * Requires an app access token.
 */

$facebook_app = $fqb->getApp();

// Make an app access token
$app_access_token = $facebook_app->getAccessToken();

$node = $fqb
    ->node($config['app_id'] . '/accounts/test-users')
    ->accessToken($app_access_token)
    ->fields('id', 'login_url')
    ->limit(10);

echo '<h1>Test Users For App ID ' .  $config['app_id']  . '</h1>' . "\n\n";
echo '<p><pre>GET ' . htmlentities($node->asUrl()) . '</pre></p>' . "\n\n";

try
{
    $response = $node->get();

    $users = $response->getGraphList();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

if (count($users) > 0)
{
    foreach ($users as $user)
    {
        var_dump($user->asArray());
    }
}
else
{
    echo 'No test users returned for app ID ' . $config['app_id'] . "\n\n";
}
