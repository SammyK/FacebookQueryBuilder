# Facebook Query Builder

[![Build Status](http://img.shields.io/travis/SammyK/FacebookQueryBuilder/master.svg)](https://travis-ci.org/SammyK/FacebookQueryBuilder)
[![Latest Stable Version](http://img.shields.io/badge/Development%20Version-2.0.0-orange.svg)](https://packagist.org/packages/sammyk/facebook-query-builder)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE)


A query builder that makes it easy to send complex & efficient [nested requests](https://developers.facebook.com/docs/graph-api/using-graph-api#fieldexpansion) to Facebook's [Graph API](https://developers.facebook.com/docs/graph-api) to get [lots of specific data back](https://www.sammyk.me/optimizing-request-queries-to-the-facebook-graph-api) with one request.

Facebook Query Builder runs on the latest [Facebook PHP SDK v4.1](https://github.com/facebook/facebook-php-sdk-v4).

Build complex nested requests...

```
@todo add example
```

...using PHP syntax.

```php
// Get the user's ID & email
$response = $fqb->node('me')->fields(['id', 'email'])->get();
```

It can also be used with `POST` and `DELETE` operations.

```php
// Post "Hello!" to a user's timeline
$response = $fqb->node('me/feed')->with(['message' => 'Hello!'])->post();

// Unlike your mom
$response = $fqb->node('your-mom-id_like-id')->delete();
```

- [Introduction](#introduction)
- [Installation](#installation)
- [Usage](#usage)
    - [Obtaining An Access Token](#obtaining-an-access-token)
    - [Setting The Access Token](#setting-the-access-token)
    - [Examples](#examples)
    - [Method Reference](#method-reference)
    - [Request Objects](#request-objects)
    - [Response Objects](#response-objects)
- [Testing](#testing)
- [Contributing](#contributing)
- [CHANGELOG](#changelog)
- [Credits](#credits)
- [License](#license)


## Introduction

The Facebook Query Builder uses the same [Graph API nomenclature](https://developers.facebook.com/docs/graph-api/quickstart#basics) for three main concepts:

1. **Node:** A node represents a "real-world thing" on Facebook like a user or a page. In the Facebook PHP SDK, nodes that are returned from a Graph response are represented as `GraphObjects`'s.
2. **Edge:** An edge is the relationship between two or more nodes. For example a "photo" node would have a "comments" edge. In the Facebook PHP SDK, edges retruned from a Graph response are represented as a list of `GraphObjects`'s called a `GraphList`.
3. **Field:** Nodes have properties associated with them. These properties are called fields. A user has an "id" and "name" field for example.

When you send a request to the Graph API, the URL is structured like so:

    /node-id/edge-name?fields=field-name

To generate the same URL with Facebook Query Builder, you'd do the following:

```php
$edge = $fqb->edge('edge-name')->fields('field-name');
echo $fqb->node('node-id')->fields($edge);
```

If you were to execute that script, you might be surprised to see the URL looks a little different because it would output:

    /node-id?fields=edge-name{field-name}

The two URL's are functionally identical. If we sent those URL's to the Graph API, the response would *almost* be the same (with a few minor differences). What makes the URL generated with Facebook Query Builder different is that it is being expressed as a [nested request](https://developers.facebook.com/docs/graph-api/using-graph-api#fieldexpansion).

And that is what makes Facebook Query Builder so powerful. It does the heavy lifting to generate properly formatted nested requests from a fluent, easy-to-read PHP interface.


## Installation

Facebook Query Builder is installed using [Composer](https://getcomposer.org/). Add the Facebook Query Builder package to your `composer.json` file.

```json
{
    "require": {
        "sammyk/facebook-query-builder": "~2.0@dev",
        "facebook/php-sdk-v4": "~4.1.0@dev"
    }
}
```

> **Note:** The Facebook PHP SDK v4.1 is still in dev mode but has reached a feature-freeze until it is tagged as stable so there shouldn't be any breaking changes. :) But because it's in dev mode you'll need to require it explicitly in your require using the `@dev` minimum stability flag since [composer won't pull in a dev mode dependency of a dependency](https://getcomposer.org/doc/faqs/why-can%27t-composer-load-repositories-recursively.md).


## Usage

Since `FQB` uses the [decorator pattern](http://sourcemaking.com/design_patterns/Decorator/php) to provide additional functionality to the native [Facebook PHP SDK v4.1](https://github.com/facebook/facebook-php-sdk-v4/tree/master). After [creating an app in Facebook](https://developers.facebook.com/apps), you'll need to create an instance of the `Facebook\Facebook` super service class from the native Facebook PHP SDK and inject it into `FQB`'s constructor.

```php
$fb = new Facebook\Facebook([
    'app_id' => 'your-app-id',
    'app_secret' => 'you-app-secret',
    'default_graph_version' => 'v2.2',
    ]);

$fqb = new SammyK\FacebookQueryBuilder\FQB($fb);
```


## Obtaining An Access Token

Most calls to Graph require an access token. You will need to obtain an access token using the functionality provided in the native Facebook PHP SDK.

The documentation for obtaining an access token via the Facebook PHP SDK is very good. Consult the documentation for obtaining an access token:

- [From a redirect (OAuth 2.0)](https://github.com/facebook/facebook-php-sdk-v4/blob/master/docs/FacebookRedirectLoginHelper.fbmd)
- [From within an app canvas](https://github.com/facebook/facebook-php-sdk-v4/blob/master/docs/FacebookCanvasHelper.fbmd)
- [From within a page tab](https://github.com/facebook/facebook-php-sdk-v4/blob/master/docs/FacebookPageTabHelper.fbmd)
- [From the JavaScript SDK](https://github.com/facebook/facebook-php-sdk-v4/blob/master/docs/FacebookJavaScriptHelper.fbmd)


## The AccessToken Entity

After you obtain an access token, you can set it as the default fall-back access token using the native Facebook PHP SDK and `FQB` will use that for every request made to the Graph API.

```php
$fb->setDefaultAccessToken('my-access-token');
$response = $fqb->node('me')->get();
```

Alternatively you can use the `accessToken()` method in `FQB` to set an access to use for a specific request.

```php
$response = $fqb->node('me')->accessToken('my-access-token')->get();
```

> **Note:** As of version 2.0 of the Facebook Query Builder, the `AccessToken` object was ported to the official Facebook PHP SDK v4.1. Refer to the [official documentation](https://github.com/facebook/facebook-php-sdk-v4/blob/master/docs/AccessToken.fbmd) for full details.


## Examples


### Get a user node

A basic example that gets the logged in user's profile.

```php
$user_request = $fqb->node('me')->fields(['id', 'email']);

try {
    $response = $user_request->get();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

$user = $response->getGraphUser();

// If you want to see what the request looked like:
var_dump($user_request->asUrl());
```

### Get data across multiple edges

The bread and butter of the Facebook Query Builder is its support for [nested requests](https://developers.facebook.com/docs/graph-api/using-graph-api/v2.0#fieldexpansion). Nested requests allow you to get a lot of data from the Graph API with just one request.

The following example will get the logged in user's name & first 5 photos they are tagged in with just one call to Graph.

```php
$photos = $fqb->edge('photos')->fields('id', 'source')->limit(5);
$user = $fqb->node('me')->fields('name', $photos);

try {
    $response = $user->get();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
```

And edges can have other edges embedded in them to allow for infinite deepness. This allows you to do fairly complex calls to Graph while maintaining very readable code.

The following example will get a user's name, and first 10 photos they are tagged in. For each photo it gets the first 2 comments and all the likes.

```php
$likes = $fqb->edge('likes');
$comments = $fqb->edge('comments')->fields('message')->limit(2);
$photos = $fqb->edge('photos')->fields('id', 'source', $comments, $likes)->limit(10);

$user = $fqb->node('user_id')->fields('name', $photos);

try {
    $response = $user->get();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
```


### More Examples

Check out the [/examples](https://github.com/SammyK/FacebookQueryBuilder/tree/master/examples) directory to see more detailed examples.

To get the examples to work you'll need to duplicate the `/examples/config.php.dist` to `/examples/config.php` and enter your app credentials and access token.


## Method Reference

- [node()](#node)
- [get()](#get)
- [post()](#post)
- [delete()](#delete)
- [edge()](#edge)
- [fields()](#fields)
- [modifiers()](#modifiers)
- [limit()](#limit)
- [withPostData()](#withpostdata)
- [search()](#search)
- [etag()](#etag)
- [accessToken()](#accesstoken)
- [asUrl()](#asurl)
- [asGetRequest()](#asgetrequest)
- [asPostRequest()](#aspostrequest)
- [asDeleteRequest()](#asdeleterequest)
- [sendBatchRequest()](#sendbatchrequest)


### node()

```php
node(string $graph_node_name): FQB
```

Returns a new instance of the `FQB` entity. Any valid Graph node or endpoint on the Graph API can be passed to `node()`.

```php
$me_photos_node = $fqb->node('me/photos');
```

### get()

```php
get(void): FacebookResponse
```

Performs a `GET` request to Graph and returns the response in the form of a collection. Will throw an `FacebookQueryBuilderException` if something went wrong while trying to communicate with Graph.

```php
// Get the logged in user's profile
$user = $fqb->node('me')->get();

// Get a list of test users for this app
$test_users = $fqb->node('my_app_id/accounts/test-users')->get();
```


### post()

```php
post(void): FacebookResponse
```

Sends a `POST` request to Graph and returns the response in the form of a collection. Will throw an `FacebookQueryBuilderException` if something went wrong while trying to communicate with Graph.

```php
// Update a page's profile
$new_about_data = ['about' => 'This is the new about section!'];

$response = $fqb->node('page_id')->with($new_about_data)->post();


// Like a photo
$response = $fqb->node('photo_id/likes')->post();


// Post a status update for the logged in user
$data = ['message' => 'My witty status update.'];

$response = $fqb->node('me/feed')->with($data)->post();
$status_update_id = $response['id'];


// Post a comment to a status update
$comment = ['message' => 'My witty comment on your status update.'];

$response = $fqb->node('status_update_id/comments')->with($comment)->post();
```


### delete()

```php
delete(void): FacebookResponse
```

Sends a `DELETE` request to Graph and returns the response in the form of a collection. Will throw an `FacebookQueryBuilderException` if something went wrong while trying to communicate with Graph.

```php
// Delete a comment
$response = $fqb->node('comment_id')->delete();

// Unlike a photo
$response = $fqb->node('photo_id/likes')->delete();
```


### edge()

```php
node(string $edge_name): GraphEdge
```

Returns an `GraphEdge` value object to be passed to the `fields()` method.


### fields()

```php
node(mixed $field_name_or_edge[, mixed $field_name_or_edge[, ...]]): FQB
```

Set the fields and edges for this `GraphEdge`. The fields and edges can be passed as an array or list of arguments.

```php
$edge_one = $fqb->edge('my_edge')->fields('my_field', 'my_other_field');
$edge_two = $fqb->edge('my_edge')->fields(['field_one', 'field_two']);

$obj = $fqb->node('some_object')->fields('some_field', $edge_one, $edge_two)->get();
```


### limit()

```php
node(int $number_of_results_to_return): FQB
```

Limit the number of results returned from Graph.

```php
$edge = $fqb->edge('some_edge')->limit(7);
```


### withPostData()

```php
withPostData(array $data_to_post): FQB
```

The array should be an associative array. The key should be the name of the field as defined by Facebook.

If used in conjunction with the `post()` method, the data will be sent in the body of the request.

```php
// Post a new comment to a photo
$comment_data = ['message' => 'My new comment!'];

$response = $fqb->node('photo_id')->with($comment_data)->post('comments');

// Update an existing comment
$comment_data = ['message' => 'My updated comment.'];

$response = $fqb->node('comment_id')->with($comment_data)->post();
```


### modifiers()

```php
modifiers(array $modifiers): FQB
```

Modifiers are @todo Talk about that here...

If used in conjunction with a `get()` request, the data will be appended in the URL either in the sub edge or root edge.

```php
// Get the large version of a page profile picture
$profile_picture = $fqb->edge('picture')->with(['type' => 'large']);
$page_info = $fqb->node('some_page_id')->fields('name', $profile_picture)->get();
```


### search()

```php
search(string $search_query[, string $type_of_search]): FQB
```

You can easily search Graph with the `search()` method.

The first argument is your search query. The second argument is the optional type of Graph object you are searching for. See the [Facebook documentation for a full list of supported search objects](https://developers.facebook.com/docs/graph-api/using-graph-api/v2.0#searchtypes).

```php
// Search for users named Bill
$list_of_users = $fqb->search('Bill', 'user')->get();

// Search for coffee joints near San Fransisco
$list_of_locations = $fqb->search('coffee', 'place')
    ->with([
        'center' => '37.76,-122.427',
        'distance' => '1000'
    ])
    ->get();
```

See more [search examples](https://github.com/SammyK/FacebookQueryBuilder/tree/master/examples/search_graph.php).


## Request Objects

Requests sent to Graph are represented by 2 value objects, `RootEdge` & `Edge`. Each object represents a segment of the URL that will eventually be compiled, formatted as a string, and sent to Graph with either the `get()` or `post()` method.


### RootEdge

For debugging, you can access `RootEdge` as a string to get the URL that will be sent to Graph.

```php
$root_edge = $fqb->node('me')->fields('id', 'email');

echo $root_edge;
```

The above example will output:

    /me?fields=id,email


### Edge

An `Edge` has the same properties as a `RootEdge` but it will be formatted using [nested-request syntax](https://developers.facebook.com/docs/graph-api/using-graph-api/v2.0#fieldexpansion) when it is converted to a string.

```php
$photos = $fqb->edge('photos')->fields('id', 'source')->limit(5);

echo $photos;
```

The above example will output:

    photos.limit(5).fields(id,source)

`Edge`'s can be embedded into other `Edge`'s.

```php
$photos = $fqb->edge('photos')->fields('id', 'source')->limit(5);
$root_edge = $fqb->node('me')->fields('email', $photos);

echo $root_edge;
```

The above example will output:

    /me?fields=email,photos.limit(5).fields(id,source)


## Responses

All responses from the `get()`, `post()`, and `delete()` methods return a `Facebook\FacebookResponse` from the native Facebook PHP SDK.

```php
$response = $fqb->node('me')->fields('email', 'photos')->get();

$user = $response->getGraphUser();

// Access properties like an array
$email = $user['email'];

// Get data as array
$user_array = $user->asArray();

// Get data as JSON string
$user_json = $user->asJson();

// Iterate through the values
foreach ($user['photos'] as $photo) {
    // . . .
}

// Morph the data with a closure
$user['photos']->each(function ($value) {
    $value->new_height = $value->height + 22;
});
```


## Testing

Just run `phpunit` from the root directory of this project.

``` bash
$ ./vendor/bin/phpunit
```


## Contributing

Please see [CONTRIBUTING](https://github.com/SammyK/FacebookQueryBuilder/blob/master/CONTRIBUTING.md) for details.


## CHANGELOG

Please see [CHANGELOG](https://github.com/SammyK/FacebookQueryBuilder/blob/master/CHANGELOG.md) for history.


## Credits

- [Sammy Kaye Powers](https://github.com/SammyK)
- [All Contributors](https://github.com/SammyK/FacebookQueryBuilder/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE) for more information.
