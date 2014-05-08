<?php

require_once __DIR__ . '/bootstrap.php';

use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

/**
 * Get the logged in user's profile.
 */
try
{
    $user = $fqb->object('me')->get();

    var_dump($user);
}
catch (FacebookQueryBuilderException $e)
{
    var_dump($e->getResponse());
}
