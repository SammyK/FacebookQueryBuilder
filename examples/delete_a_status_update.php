<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Delete a status update for the logged in user.
 * Requires an access token with the "publish_actions" extended permission.
 */

// First, create a status update that we can delete
$status_update = ['message' => 'This status update won\'t last long! (That\'s what she said.)'];
$node = $fqb->node('me/feed')->withPostData($status_update);

echo '<h1>Post Status Update:</h1>' . "\n\n";
echo '<p><pre>POST ' . htmlentities($node->asUrl()) . "\n\n" . print_r($status_update, true) . '</pre></p>' . "\n\n";

try
{
    $response = $node->post();
    $graphObject = $response->getGraphObject();

    var_dump($graphObject->asArray());

    $status_update_id = $graphObject['id'];
}
catch (FacebookResponseException $e)
{
    echo '<p>Oops! Make sure you have the "publish_actions" extended permission for this access token.' . "\n\n";

    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

echo '<hr />' . "\n\n";

$node = $fqb->node($status_update_id);

echo '<h1>Delete Status Update:</h1>' . "\n\n";
echo '<p><pre>DELETE ' . htmlentities($node->asUrl()) . '</pre></p>' . "\n\n";

try
{
    // BALETED!
    $response = $node->delete();
    $graphObject = $response->getGraphObject();

    var_dump($graphObject->asArray());
}
catch (FacebookResponseException $e)
{
    echo '<p>Oops! Make sure you have the "publish_actions" extended permission for this access token.' . "\n\n";

    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}
