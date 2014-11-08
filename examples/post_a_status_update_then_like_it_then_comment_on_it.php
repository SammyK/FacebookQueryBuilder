<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Post a status update for the logged in user. Then like it. Then comment on it.
 * Requires an access token with the "publish_actions" extended permission.
 */

// Post the status update
$status_update = ['message' => 'My witty status update.'];
$node = $fqb->node('me/feed')->withPostData($status_update);

echo '<h1>Post A Status Update</h1>' . "\n\n";
echo '<p><pre>POST ' . htmlentities($node->asUrl()) . "\n\n" . print_r($status_update, true) . '</pre></p>' . "\n\n";

try
{
    $response = $node->post();
    $data = $response->getGraphObject();
    var_dump($data->asArray());
    echo '<hr />';

    $status_update_id = $data['id'];

    // Like it!
    $node = $fqb->node($status_update_id . '/likes');
    echo '<h1>Like The Status Update</h1>' . "\n\n";
    echo '<p><pre>POST ' . $node->asUrl() . '</pre></p>' . "\n\n";

    $response = $node->post();
    $data = $response->getGraphObject();
    var_dump($data->asArray());
    echo '<hr />';

    // Comment on it
    $comment = ['message' => 'My witty comment on your status update.'];
    $node = $fqb->node($status_update_id . '/comments')->withPostData($comment);
    echo '<h1>Post A Comment</h1>' . "\n\n";
    echo '<p><pre>POST ' . $node->asUrl() . "\n\n" . print_r($status_update, true) . '</pre></p>' . "\n\n";

    $response = $node->post();
    $data = $response->getGraphObject();
    var_dump($data->asArray());
    echo '<hr />';

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
