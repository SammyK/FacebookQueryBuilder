<?php

session_start();

require_once __DIR__ . '/bootstrap.php';

use SammyK\FacebookQueryBuilder\FQB;
use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

try
{
    $token = $fqb->auth()->getTokenFromRedirect($config['callback_url']);
}
catch (FacebookQueryBuilderException $e)
{
    echo '<p>Error: ' . $e->getMessage() . "\n\n";
    echo '<p>Facebook SDK Said: ' . $e->getPrevious()->getMessage() . "\n\n";
    exit;
}

if ( ! $token)
{
    /**
     * No token returned. Show login link.
     */
    $scope = ['email', 'read_stream']; // Optional
    $login_url = $fqb->auth()->getLoginUrl($config['callback_url'], $scope);
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
    $token_info = $token->getInfo();
}
catch (FacebookQueryBuilderException $e)
{
    echo '<p>Error: ' . $e->getMessage() . "\n\n";
    echo '<p>Facebook SDK Said: ' . $e->getPrevious()->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

var_dump($token_info->toArray());

if ( ! $token->isLongLived())
{
    /**
     * Extend the access token.
     */
    try
    {
        $token = $token->extend();
    }
    catch (FacebookQueryBuilderException $e)
    {
        echo '<p>Error: ' . $e->getMessage() . "\n\n";
        echo '<p>Facebook SDK Said: ' . $e->getPrevious()->getMessage() . "\n\n";
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
        $token_info = $token->getInfo();
    }
    catch (FacebookQueryBuilderException $e)
    {
        echo '<p>Error: ' . $e->getMessage() . "\n\n";
        echo '<p>Facebook SDK Said: ' . $e->getPrevious()->getMessage() . "\n\n";
        echo '<p>Graph Said: ' .  "\n\n";
        var_dump($e->getResponse());
        exit;
    }

    var_dump($token_info->toArray());
}

FQB::setAccessToken($token);

/**
 * Get the logged in user's profile.
 */
try
{
    $user = $fqb->object('me')->get();
}
catch (FacebookQueryBuilderException $e)
{
    echo '<p>Error: ' . $e->getMessage() . "\n\n";
    echo '<p>Facebook SDK Said: ' . $e->getPrevious()->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

echo '<h1>User Data</h1>' . "\n\n";
var_dump($user->toArray());
