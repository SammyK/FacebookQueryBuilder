<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Generate some requests and then send them in a batch request.
 */

// Get the name of the logged in user
$request_user_name = $node = $fqb->node('me')->fields(['id', 'name'])->asGetRequest();

// Get user likes
$request_user_likes = $node = $fqb->node('me/likes')->fields(['id', 'name'])->limit(1)->asGetRequest();

// Get user events
$request_user_event = $node = $fqb->node('me/events')->fields(['id', 'name'])->limit(2)->asGetRequest();

// Post a status update with reference to the user's name
$message = 'My name is {result=user-profile:$.name}.' . "\n\n";
$message .= 'I like this page: {result=user-likes:$.data.0.name}.' . "\n\n";
$message .= 'My next 2 events are {result=user-events:$.data.*.name}.';
$status_update = ['message' => $message];
$request_post_to_feed = $fqb->node('me/feed')->withPostData($status_update)->asPostRequest();

// Get user photos
$request_user_photos = $node = $fqb->node('me/photos')->fields(['id', 'source', 'name'])->limit(2)->asGetRequest();

$batch = [
    'user-profile' => $request_user_name,
    'user-likes' => $request_user_likes,
    'user-events' => $request_user_event,
    'post-to-feed' => $request_post_to_feed,
    'user-photos' => $request_user_photos,
    ];

echo '<h1>Make a batch request</h1>' . "\n\n";

try
{
    $responses = $fqb->sendBatchRequest($batch);
}
catch (FacebookResponseException $e)
{
    echo '<p>Oops! Make sure you have the "publish_actions" extended permission for this access token.' . "\n\n";

    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

foreach ($responses as $response)
{
    if ($response->isError()) {
        $e = $response->getThrownException();
        echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
        echo '<p>Graph Said: ' . "\n\n";
        var_dump($e->getResponse());
    }
    else
    {
        var_dump('HTTP status code: ' . $response->getHttpStatusCode());
        var_dump($response->getBody());
        echo '<hr />' . "\n\n";
    }
}

echo '<h1>Now check your profile...</h1>' . "\n\n";
echo '<p>You should see something like this...</p>' . "\n\n";
echo '<p><img src="https://sammyk.s3.amazonaws.com/open-source/facebook-query-builder/batch-request.png" width="465" height="204" alt=""/></p>';
