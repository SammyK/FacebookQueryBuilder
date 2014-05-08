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
    $response = $fqb->object('me/feed')->with($status_update)->post();
    var_dump('Post Status Update Response:', $response);

    $status_update_id = $response['id'];

    // BALETED!
    $response = $fqb->object($status_update_id)->delete();
    var_dump('Delete Status Update Response:', $response);
}
catch (FacebookQueryBuilderException $e)
{
    var_dump($e->getResponse());
}
