<?php

require_once __DIR__ . '/bootstrap.php';

use SammyK\FacebookQueryBuilder\FQB;
use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

/**
 * Gets a list of test users for an app.
 * Requires an app access token.
 */

// Make an app access token
FQB::setAccessToken($config['app_id'] . '|' . $config['app_secret']);

try
{
    $users = $fqb->object($config['app_id'] . '/accounts/test-users')->fields('id','login_url')->limit(10)->get();

    $users->each(function ($v)
    {
        var_dump($v->toArray());
    });
}
catch (FacebookQueryBuilderException $e)
{
    var_dump($e->getResponse());
}
