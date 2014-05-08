<?php

require_once __DIR__ . '/bootstrap.php';

use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

/**
 * Gets a list of status updates for the logged in user.
 * Requires an access token with the "read_stream" extended permission.
 */

try
{
    $statuses = $fqb->object('me/statuses')->limit(10)->get();

    $statuses->each(function ($v)
    {
        var_dump($v['message']);
    });
}
catch (FacebookQueryBuilderException $e)
{
    var_dump($e->getResponse());
}
