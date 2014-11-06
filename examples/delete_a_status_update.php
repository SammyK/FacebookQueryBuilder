<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Delete a status update for the logged in user.
 * Requires an access token with the "publish_actions" extended permission.
 */

try
{
    // First, create a status update that we can delete
    $status_update = ['message' => 'This status update won\'t last long! (That\'s what she said.)'];
    $request = $fqb->node('me/feed')->with($status_update);
    $response = $request->post()->getGraphObject();

    echo '<h1>Post Status Update:</h1>' . "\n\n";
    echo '<h2>Request URL</h2>' . "\n\n";
    echo '<p><pre>' . (string) $request . '</pre></p>' . "\n\n";
    echo '<h2>Response from Graph</h2>' . "\n\n";
    var_dump($response->asArray());

    $status_update_id = $response['id'];

    // BALETED!
    $request = $fqb->node($status_update_id);
    $response = $request->delete()->getGraphObject();
    echo '<h1>Delete Status Update:</h1>' . "\n\n";
    echo '<h2>Request URL</h2>' . "\n\n";
    echo '<p><pre>' . (string) $request . '</pre></p>' . "\n\n";
    echo '<h2>Response from Graph</h2>' . "\n\n";
    var_dump($response->asArray());
}
catch (FacebookResponseException $e)
{
    echo '<p>Oops! Make sure you have the "publish_actions" extended permission for this access token.' . "\n\n";

    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}
