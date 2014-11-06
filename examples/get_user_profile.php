<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Get the logged in user's profile.
 */
try
{
    $response = $fqb->node('me')->get();
    $user = $response->getGraphUser();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

echo '<h1>Logged In User\'s Profile</h1>' . "\n\n";
var_dump($user->asArray());
