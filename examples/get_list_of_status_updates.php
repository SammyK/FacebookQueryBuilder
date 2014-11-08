<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Gets a list of status updates for the logged in user.
 * Requires an access token with the "read_stream" extended permission.
 */

$node = $fqb
    ->node('me/statuses')
    ->limit(10);

echo '<h1>Last 10 Statuses</h1>' . "\n\n";
echo '<p><pre>GET ' . htmlentities($node->asUrl()) . '</pre></p>' . "\n\n";

try
{
    $response = $node->get();
    $statuses = $response->getGraphList();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

if (count($statuses) > 0)
{
    foreach ($statuses as $status)
    {
        var_dump($status['message']);
    }
}
else
{
    echo 'No statuses returned. Make sure you have the "read_stream" extended permission for this access token.' . "\n\n";
}