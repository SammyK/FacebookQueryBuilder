<?php

session_start();

require_once __DIR__ . '/bootstrap.php';

use Facebook\Helpers\FacebookRedirectLoginHelper;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

$facebookApp = $fqb->getApp();
$facebookClient = $fqb->getClient();
$redirectHelper = new FacebookRedirectLoginHelper($facebookApp);

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
    $scope = ['email', 'read_stream']; // Optional
    $login_url = $redirectHelper->getLoginUrl($config['callback_url'], $scope);

    echo '<a href="' . $login_url . '">Log in with Facebook</a>';
    exit;
}

echo '<h1>Returned AccessToken Object</h1>' . "\n\n";
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

if ( ! $token->isLongLived())
{
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

    echo '<h1>Long-lived AccessToken Object</h1>' . "\n\n";
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
}

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

echo '<h1>User Data</h1>' . "\n\n";
var_dump($user->asArray());
