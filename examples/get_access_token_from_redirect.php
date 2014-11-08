<?php

session_start();

require_once __DIR__ . '/bootstrap.php';

use Facebook\Helpers\FacebookRedirectLoginHelper;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

$facebookApp = $fqb->getApp();
$facebookClient = $fqb->getClient();
$redirectHelper = new FacebookRedirectLoginHelper($facebookApp);

echo '<h1>Get AccessToken From Redirect</h1>' . "\n\n";

try
{
    $token = $redirectHelper->getAccessToken($facebookClient, $config['callback_url']);
}
catch (FacebookSDKException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    exit;
}

if ( ! $token)
{
    /**
     * No token returned. Show login link.
     */
    $scope = [ // Optional
        'email',
        'user_events',
        'user_likes',
        'user_status',
        'user_photos',
        'read_stream',
        'publish_actions',
        ];
    $login_url = $redirectHelper->getLoginUrl($config['callback_url'], $scope);

    echo '<a href="' . $login_url . '">Log in with Facebook</a>';
    exit;
}

var_dump($token);

/**
 * Get info about the access token.
 */
try
{
    $token_info = $token->getInfo($facebookApp, $facebookClient);
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

var_dump($token_info->asArray());

echo '<hr />' . "\n\n";

if ( ! $token->isLongLived())
{
    echo '<h1>Long-lived AccessToken Object</h1>' . "\n\n";

    /**
     * Extend the access token.
     */
    try
    {
        $token = $token->extend($facebookApp, $facebookClient);
    }
    catch (FacebookResponseException $e)
    {
        echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
        echo '<p>Graph Said: ' .  "\n\n";
        var_dump($e->getResponse());
        exit;
    }

    var_dump($token);

    /**
     * Get info about the access token.
     */
    try
    {
        $token_info = $token->getInfo($facebookApp, $facebookClient);
    }
    catch (FacebookResponseException $e)
    {
        echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
        echo '<p>Graph Said: ' .  "\n\n";
        var_dump($e->getResponse());
        exit;
    }

    var_dump($token_info->asArray());

    echo '<hr />' . "\n\n";
}

echo '<h1>User Data</h1>' . "\n\n";

/**
 * Get the logged in user's profile.
 */
try
{
    $user = $fqb
        ->node('me')
        ->accessToken($token)
        ->get()
        ->getGraphUser();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

var_dump($user->asArray());
