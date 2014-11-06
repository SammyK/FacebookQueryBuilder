<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Post a status update for the logged in user. Then like it. Then comment on it.
 * Requires an access token with the "publish_actions" extended permission.
 */

try
{
    // Post the status update
    $status_update = ['message' => 'My witty status update.'];
    $response = $fqb->node('me/feed')->with($status_update)->post();
    $data = $response->getGraphObject();
    echo '<h1>Post Status Update Response</h1>' . "\n\n";
    var_dump($data->asArray());

    $status_update_id = $data['id'];

    // Like it!
    $response = $fqb->node($status_update_id . '/likes')->post();
    $data = $response->getGraphObject();
    echo '<h1>Like Status Update Response</h1>' . "\n\n";
    var_dump($data->asArray());

    // Comment on it
    $comment = ['message' => 'My witty comment on your status update.'];
    $response = $fqb->node($status_update_id . '/comments')->with($comment)->post();
    $data = $response->getGraphObject();
    echo '<h1>Post Comment Response</h1>' . "\n\n";
    var_dump($data->asArray());

    echo '<h1>Now check your profile...</h1>' . "\n\n";
    echo '<p>You should see something like this...</p>' . "\n\n";
    echo '<p><img src="https://sammyk.s3.amazonaws.com/open-source/facebook-query-builder/status-update-example.png" width="467" height="247" alt=""/></p>';
}
catch (FacebookResponseException $e)
{
    echo '<p>Oops! Make sure you have the "publish_actions" extended permission for this access token.' . "\n\n";

    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}
