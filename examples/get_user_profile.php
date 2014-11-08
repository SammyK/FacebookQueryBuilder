<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Get the logged in user's profile.
 */

$node = $fqb->node('me');

echo '<h1>Logged In User\'s Profile</h1>' . "\n\n";
echo '<p><pre>GET ' . htmlentities($node->asUrl()) . '</pre></p>' . "\n\n";

try
{
    $response = $node->get();
    $user = $response->getGraphUser();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

var_dump($user->asArray());
