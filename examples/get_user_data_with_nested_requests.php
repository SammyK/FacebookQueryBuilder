<?php

require_once __DIR__ . '/bootstrap.php';

use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

/**
 * Get more info on the logged in user with just one call to graph.
 * Requires an access token with the "user_photos", "user_likes", "user_events" extended permissions.
 */
try
{
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
    $user_data = $fqb->object('me')
        ->fields('name', 'updated_time', $photos_user_tagged_in, $pages_user_likes, $events_user_attending)
        ->get();

    var_dump($user_data['name']);
    var_dump('Last Update: ' . $user_data['updated_time']->diffForHumans());

    var_dump('Photos:');
    foreach ($user_data['photos'] as $photo)
    {
        var_dump($photo);
    }

    var_dump('Liked Pages:');
    foreach ($user_data['likes'] as $page)
    {
        var_dump($page);
    }

    var_dump('Events:');
    foreach ($user_data['events'] as $event)
    {
        var_dump($event);
    }
}
catch (FacebookQueryBuilderException $e)
{
    var_dump($e->getResponse());
}
