<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Search for West Coast Swing Facebook pages and paginate based on offset pagination.
 * @see https://developers.facebook.com/docs/graph-api/using-graph-api#offset
 */

$limit = 5;
$offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? $_GET['offset'] : 0;
$next_offset = $offset + $limit;

// Get the large version of the page profile picture
$profile_picture = $fqb->edge('picture')->modifiers(['type' => 'large']);
$node = $fqb->search('West Coast Swing', 'page')
                ->fields('id', 'name', 'link', $profile_picture)
                ->modifiers(['offset' => $offset])
                ->limit($limit);

echo '<h1>Search for "West Coast Swing" Facebook pages</h1>' . "\n\n";
echo '<p><pre>GET ' . htmlentities($node->asUrl()) . '</pre></p>' . "\n\n";

try
{
    $response = $node->get();
}
catch (FacebookResponseException $e)
{
    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

$list_of_pages = $response->getGraphList();
if (count($list_of_pages) > 0)
{
    foreach ($list_of_pages as $page)
    {
        var_dump($page->asArray());
    }

    echo '<hr />' . "\n\n";;
    echo '<a href="paginate_results.php?offset=' . $next_offset .'">Next Page &gt;</a>' . "\n\n";;
}
else
{
    echo 'No results for "West Coast Swing" found' . "\n\n";
}
