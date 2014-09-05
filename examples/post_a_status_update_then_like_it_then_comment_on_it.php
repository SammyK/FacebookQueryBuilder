<?php

require_once __DIR__ . '/bootstrap.php';

use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

/**
 * Post a status update for the logged in user. Then like it. Then comment on it.
 * Requires an access token with the "publish_actions" extended permission.
 */

try
{
    // Post the status update
    $status_update = ['message' => 'My witty status update.'];
    $response = $fqb->object('me/feed')->with($status_update)->post();
    echo '<h1>Post Status Update Response</h1>' . "\n\n";
    var_dump($response->toArray());

    $status_update_id = $response['id'];

    // Like it!
    $response = $fqb->object($status_update_id . '/likes')->post();
    echo '<h1>Like Status Update Response</h1>' . "\n\n";
    var_dump($response->toArray());

    // Comment on it
    $comment = ['message' => 'My witty comment on your status update.'];
    $response = $fqb->object($status_update_id . '/comments')->with($comment)->post();
    echo '<h1>Post Comment Response</h1>' . "\n\n";
    var_dump($response->toArray());

    echo '<h1>Now check your profile...</h1>' . "\n\n";
    echo '<p>You should see something like this...</p>' . "\n\n";
    echo '<p><img src="https://sammyk.s3.amazonaws.com/open-source/facebook-query-builder/status-update-example.png" width="467" height="247" alt=""/></p>';
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
