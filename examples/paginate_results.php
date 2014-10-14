<?php

require_once __DIR__ . '/bootstrap.php';

use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

/**
 * Search for West Coast Swing Facebook pages and paginate based on offset pagination.
 * @see https://developers.facebook.com/docs/graph-api/using-graph-api#offset
 */

$limit = 5;
$offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? $_GET['offset'] : 0;
$next_offset = $offset + $limit;

try
{
    // Get the large version of the page profile picture
    $profile_picture = $fqb->edge('picture')->with(['type' => 'large']);
    $list_of_pages = $fqb->search('West Coast Swing', 'page')
        ->fields('id', 'name', 'link', $profile_picture)
        ->with(['offset' => $offset])
        ->limit($limit)
        ->get();
}
catch (FacebookQueryBuilderException $e)
{
    echo '<p>Error: ' . $e->getMessage() . "\n\n";
    echo '<p>Facebook SDK Said: ' . $e->getPrevious()->getMessage() . "\n\n";
    echo '<p>Graph Said: ' .  "\n\n";
    var_dump($e->getResponse());
    exit;
}

if (count($list_of_pages) > 0)
{
    echo '<h1>Search for "West Coast Swing" Facebook pages</h1>' . "\n\n";
    foreach ($list_of_pages as $page)
    {
        var_dump($page->toArray());
    }

    echo '<hr />' . "\n\n";;
    echo '<a href="paginate_results.php?offset=' . $next_offset .'">Next Page &gt;</a>' . "\n\n";;
}
else
{
    echo 'No results for "West Coast Swing" found' . "\n\n";
}
