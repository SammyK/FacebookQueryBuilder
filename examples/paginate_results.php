<?php

require_once __DIR__ . '/bootstrap.php';

use Facebook\Exceptions\FacebookResponseException;

/**
 * Search for PHP Facebook pages and paginate based default pagination from the Facebook PHP SDK.
 */

$limit = 5;
$max_pages = 5;

// Get the large version of the page profile picture
$profile_picture = $fqb->edge('picture')->modifiers(['type' => 'large']);
$node = $fqb->search('PHP', 'page')
                ->fields('id', 'name', 'link', $profile_picture)
                ->limit($limit);

echo '<h1>Search for "PHP" Facebook pages</h1>' . "\n\n";
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
    $page_count = 0;

    do
    {
        echo '<h1>Page #' . $page_count . ':</h1>' . "\n\n";

        foreach ($list_of_pages as $page)
        {
            var_dump($page->asArray());

            $likes = $page['likes'];
            do
            {
                echo '<p>Likes:</p>' . "\n\n";
                var_dump($likes->asArray());
            }
            while ($likes = $fqb->next($likes));
        }
        $page_count++;
    }
    while ($page_count < $max_pages && $list_of_pages = $fqb->next($list_of_pages));


    echo '<hr />' . "\n\n";
    echo '<a href="paginate_results.php?offset=' . $next_offset .'">Next Page &gt;</a>' . "\n\n";
}
else
{
    echo 'No results for "PHP" found' . "\n\n";
}
