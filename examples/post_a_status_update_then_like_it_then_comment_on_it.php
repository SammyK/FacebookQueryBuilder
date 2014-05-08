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
    var_dump('Post Status Update Response:', $response);

    $status_update_id = $response['id'];

    // Like it!
    $response = $fqb->object($status_update_id . '/likes')->post();
    var_dump('Like Status Update Response:', $response);

    // Comment on it
    $comment = ['message' => 'My witty comment on your status update.'];
    $response = $fqb->object($status_update_id . '/comments')->with($comment)->post();
    var_dump('Post Comment Response:', $response);
}
catch (FacebookQueryBuilderException $e)
{
    var_dump($e->getResponse());
}
