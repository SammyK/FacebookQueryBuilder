<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Search for a user named "Bill".
 */

try
{
    $response = $fqb->search('Bill', 'user')->limit(10)->get();
    $list_of_users = $response->getGraphList();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

if (count($list_of_users) > 0)
{
    echo '<h1>Search for "Bill"</h1>' . "\n\n";
    foreach ($list_of_users as $user)
    {
        var_dump($user->asArray());
    }
}
else
{
    echo 'No results for "Bill" found' . "\n\n";
}

/**
 * Search for coffee in a general area.
 */

try
{
    $response = $fqb->search('coffee', 'place')
        ->with([
                'center' => '37.76,-122.427',
                'distance' => '1000',
            ])
        ->limit(10)
        ->get();
    $list_of_locations = $response->getGraphList();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

if (count($list_of_locations) > 0)
{
    echo '<h1>Search for "Coffee" near "San Francisco, CA"</h1>' . "\n\n";
    foreach ($list_of_locations as $location)
    {
        var_dump($location->asArray());
    }
}
else
{
    echo 'No results for "Coffee" found' . "\n\n";
}

/**
 * Search for Dr. Who fan pages.
 */

try
{
    // Get the large version of the page profile picture
    $profile_picture = $fqb->edge('picture')->with(['type' => 'large']);
    $response = $fqb
        ->search('Dr. Who', 'page')
        ->fields('id', 'name', 'link', $profile_picture)
        ->limit(10)
        ->get();
    $list_of_pages = $response->getGraphList();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

if (count($list_of_pages) > 0)
{
    echo '<h1>Search for "Dr. Who" fan pages</h1>' . "\n\n";
    foreach ($list_of_pages as $page)
    {
        var_dump($page->asArray());
    }
}
else
{
    echo 'No results for "Dr. Who" found' . "\n\n";
}
