<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Get more info on the logged in user with just one call to graph.
 * Requires an access token with the "user_photos", "user_likes", "user_events" extended permissions.
 * @see https://developers.facebook.com/docs/graph-api/using-graph-api/#fieldexpansion
 */

// Get first 5 photos the user is tagged in
$photos_user_tagged_in = $fqb
    ->edge('photos')
    ->fields('name', 'source')
    ->limit(5);

// Get first 3 pages this user likes
$pages_user_likes = $fqb
    ->edge('likes')
    ->fields('name', 'link')
    ->limit(3);

// Get first 4 events that this user is attending
// And first 2 photos from each event
$event_photos = $fqb
    ->edge('photos')
    ->fields('name', 'source')
    ->limit(2);
$events_user_attending = $fqb
    ->edge('events')
    ->fields('name', 'start_time', 'end_time', $event_photos)
    ->limit(4);

// Get the logged in user's name, last profile update time, and all those edges
$node = $fqb
    ->node('me')
    ->fields('name', 'updated_time', $photos_user_tagged_in, $pages_user_likes, $events_user_attending);

echo '<h1>Get user, photos, pages and events</h1>' . "\n\n";
echo '<p><pre>GET ' . htmlentities($node->asUrl()) . '</pre></p>' . "\n\n";

try
{
    $response = $node->get();
    $user_data = $response->getGraphUser();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

echo '<h1>User Data</h1>' . "\n\n";
echo '<p>Name: ' . $user_data['name'] .  "\n\n";
echo '<p>Last Update: ' . $user_data['updated_time']->format('r') .  "\n\n";

echo '<hr />' . "\n\n";

echo '<h1>Last 5 Photos</h1>' . "\n\n";
if (isset($user_data['photos']))
{
    foreach ($user_data['photos'] as $photo)
    {
        var_dump($photo->asArray());
    }
}
else
{
    echo '<p>No photos returned. Make sure you have the "user_photos" extended permission for this access token.' . "\n\n";
}

echo '<hr />' . "\n\n";

echo '<h1>Last 3 Liked Pages</h1>' . "\n\n";
if (isset($user_data['likes']))
{
    foreach ($user_data['likes'] as $page)
    {
        var_dump($page->asArray());
    }
}
else
{
    echo '<p>No liked pages returned. Make sure you have the "user_likes" extended permission for this access token.' . "\n\n";
}

echo '<hr />' . "\n\n";

echo '<h1>Latest 4 Events and 2 Photos From Each Event</h1>' . "\n\n";
if (isset($user_data['events']))
{
    foreach ($user_data['events'] as $event)
    {
        var_dump($event->asArray());
    }
}
else
{
    echo '<p>No events returned. Make sure you have the "user_events" extended permission for this access token.' . "\n\n";
}
