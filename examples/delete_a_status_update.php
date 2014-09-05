<?php

require_once __DIR__ . '/bootstrap.php';

use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

/**
 * Delete a status update for the logged in user.
 * Requires an access token with the "publish_actions" extended permission.
 */

try
{
    // First, create a status update that we can delete
    $status_update = ['message' => 'This status update won\'t last long! (That\'s what she said.)'];
    $request = $fqb->object('me/feed')->with($status_update);
    $response = $request->post();

    echo '<h1>Post Status Update:</h1>' . "\n\n";
    echo '<h2>Request URL</h2>' . "\n\n";
    echo '<p><pre>' . (string) $request . '</pre></p>' . "\n\n";
    echo '<h2>Response from Graph</h2>' . "\n\n";
    var_dump($response->toArray());

    $status_update_id = $response['id'];

    // BALETED!
    $request = $fqb->object($status_update_id);
    $response = $request->delete();
    echo '<h1>Delete Status Update:</h1>' . "\n\n";
    echo '<h2>Request URL</h2>' . "\n\n";
    echo '<p><pre>' . (string) $request . '</pre></p>' . "\n\n";
    echo '<h2>Response from Graph</h2>' . "\n\n";
    var_dump($response->toArray());
}
catch (FacebookQueryBuilderException $e)
{
    echo '<p>Oops! Make sure you have the "publish_actions" extended permission for this access token.' . "\n\n";

    echo '<p>Error: ' . $e->getMessage() . "\n\n";
    echo '<p>Facebook SDK Said: ' . $e->getPrevious()->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}
