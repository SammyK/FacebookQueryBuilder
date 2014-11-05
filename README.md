# Facebook Query Builder

[![Build Status](http://img.shields.io/travis/SammyK/FacebookQueryBuilder.svg)](https://travis-ci.org/SammyK/FacebookQueryBuilder)
[![Latest Stable Version](http://img.shields.io/badge/Latest%20Stable-1.1.2-blue.svg)](https://packagist.org/packages/sammyk/facebook-query-builder)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE)


An elegant and efficient way to interface with Facebook's [Graph API](https://developers.facebook.com/docs/graph-api) using the latest [Facebook PHP SDK v4.0](https://github.com/facebook/facebook-php-sdk-v4). It's as easy as:

```php
$user = $fqb->object('me')->get();
```

- [Installation](#installation)
- [Usage](#usage)
    - [Obtaining An Access Token](#obtaining-an-access-token)
    - [The AccessToken Object](#the-accesstoken-object)
    - [Setting The Access Token Or FacebookSession](#setting-the-access-token-or-facebooksession)
    - [Examples](#examples)
    - [Method Reference](#method-reference)
    - [Request Objects](#request-objects)
    - [Response Objects](#response-objects)
- [Overwriting Persistent Storage](#overwriting-persistent-storage)
- [Testing](#testing)
- [TODO](#todo)
- [Contributing](#contributing)
- [CHANGELOG](#changelog)
- [Credits](#credits)
- [License](#license)


## Installation

Facebook Query Builder is installed using [Composer](https://getcomposer.org/). Add the Facebook Query Builder package to your `composer.json` file.

```json
{
    "require": {
        "sammyk/facebook-query-builder": "~1.1"
    }
}
```

Or via the command line in the root of your project installation.

```bash
$ composer require "sammyk/facebook-query-builder:~1.1"
```


## Usage

After [creating an app in Facebook](https://developers.facebook.com/apps), you'll need to provide the app ID and secret.

```php
use SammyK\FacebookQueryBuilder\FQB;

FQB::setAppCredentials('your_app_id', 'your_app_secret');

$fqb = new FQB();
```


## Obtaining An Access Token

Most calls to Graph require an access token. There are three ways to obtain an access token.

Access tokens are returned in the form of an `AccessToken` object.


### From A Redirect

The most common way to obtain an access token is to provide a login URL and get the access token on the specified callback URL.

```php
$login_url = $fqb->auth()->getLoginUrl('http://my-callback/url');
```

Then in the callback URL you can obtain the access token.

```php
use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

try
{
    $token = $fqb->auth()->getTokenFromRedirect('http://my-callback/url');
}
catch (FacebookQueryBuilderException $e)
{
    // Failed to obtain access token
    echo 'Error:' . $e->getMessage();
}
```

You can optionally send in an array of permissions to request.

```php
$scope = ['email', 'user_status'];
$login_url = $fqb->auth()->getLoginUrl('http://my-callback/url', $scope);
```

See a full example of [obtaining an access token from redirect](https://github.com/SammyK/FacebookQueryBuilder/blob/master/examples/get_access_token_from_redirect.php).


### From Within App Canvas

If you are running your app from within the context of an app canvas, you can try to obtain an access token from the signed request that Facebook sends to your app.

```php
use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

try
{
    $token = $fqb->auth()->getTokenFromCanvas();
}
catch (FacebookQueryBuilderException $e)
{
    // Failed to obtain access token
    echo 'Error:' . $e->getMessage();
}
```


### From The Javascript SDK

If you are using the Javascript SDK on your site, FQB can obtain an access token from the signed request that the Javascript SDK sets in the cookie.

```php
use SammyK\FacebookQueryBuilder\FacebookQueryBuilderException;

try
{
    $token = $fqb->auth()->getTokenFromJavascript();
}
catch (FacebookQueryBuilderException $e)
{
    // Failed to obtain access token
    echo 'Error:' . $e->getMessage();
}
```


## The AccessToken Object

By default access tokens will last for about 2 hours. You can exchange them for longer-lived tokens that last for about 60 days.

See a full example in the [obtaining an access token from redirect](https://github.com/SammyK/FacebookQueryBuilder/blob/master/examples/get_access_token_from_redirect.php) example file.


## Checking Access Token Life

```php
if ( ! $access_token->isLongLived())
{
    // Extend the short-lived token
}
```


## Extending An Access Token

```php
try
{
    $long_lived_token = $short_lived_token->extend();
}
catch (FacebookQueryBuilderException $e)
{
    // Failed to extend access token
    echo 'Error:' . $e->getMessage();
}
```


## Getting Info About An Access Token

```php
try
{
    $token_info = $access_token->getInfo();
}
catch (FacebookQueryBuilderException $e)
{
    // Failed to get access token info
    echo 'Error:' . $e->getMessage();
}
```


## Setting The Access Token Or FacebookSession

Setting the access token will new up a `FacebookSession` internally and automatically send it with all Graph calls.

You can set the access token from a string or an `AccessToken` object.

```php
FQB::setAccessToken('access_token');
// -- OR --
FQB::setAccessToken($access_token_object);
```

Alternatively, if you already have a `FacebookSession` object directly from the Facebook SDK, you can set it like so:

```php
FQB::setFacebookSession($facebook_session);
```


## Examples


### Getting a single object from Graph

Get the logged in user's profile.

```php
$user = $fqb->object('me')->fields('id','email')->get();
```

Get info from a Facebook page.

```php
$page = $fqb->object('facebook_page_id')->fields('id','name','about')->get();
```


### Nested requests

Facebook Query Builder supports [nested requests](https://developers.facebook.com/docs/graph-api/using-graph-api/v2.0#fieldexpansion) so you can get a lot more data with just one call to Graph.

> **Note:** Facebook calls endpoints on the Graph API "edges". This package adopts the same nomenclature.

The following example will get the logged in user's name & first 5 photos they are tagged in with just one call to Graph.

```php
$photos = $fqb->edge('photos')->fields('id', 'source')->limit(5);
$user = $fqb->object('me')->fields('name', $photos)->get();
```

And edges can have other edges embedded in them to allow for infinite deepness. This allows you to do fairly complex calls to Graph while maintaining very readable code.

The following example will get a user's name, and first 10 photos they are tagged in. For each photo get the first 2 comments and all the likes.

```php
$likes = $fqb->edge('likes');
$comments = $fqb->edge('comments')->fields('message')->limit(2);
$photos = $fqb->edge('photos')->fields('id', 'source', $comments, $likes)->limit(10);

$user = $fqb->object('user_id')->fields('name', $photos)->get();
```


### More Examples

Check out the [/examples](https://github.com/SammyK/FacebookQueryBuilder/tree/master/examples) directory to see more detailed examples.

To get the examples to work you'll need to duplicate the `/examples/config.php.dist` to `/examples/config.php` and enter your app credentials and access token.


## Method Reference


### object(*string* "graph_edge")

Returns a new instance of the `FQB` factory. Any valid Graph edge can be passed to `object()`.


### get()

Performs a `GET` request to Graph and returns the response in the form of a collection. Will throw an `FacebookQueryBuilderException` if something went wrong while trying to communicate with Graph.

```php
// Get the logged in user's profile
$user = $fqb->object('me')->get();

// Get a list of test users for this app
$test_users = $fqb->object('my_app_id/accounts/test-users')->get();
```


### post()

Sends a `POST` request to Graph and returns the response in the form of a collection. Will throw an `FacebookQueryBuilderException` if something went wrong while trying to communicate with Graph.

```php
// Update a page's profile
$new_about_data = ['about' => 'This is the new about section!'];

$response = $fqb->object('page_id')->with($new_about_data)->post();


// Like a photo
$response = $fqb->object('photo_id/likes')->post();


// Post a status update for the logged in user
$data = ['message' => 'My witty status update.'];

$response = $fqb->object('me/feed')->with($data)->post();
$status_update_id = $response['id'];


// Post a comment to a status update
$comment = ['message' => 'My witty comment on your status update.'];

$response = $fqb->object('status_update_id/comments')->with($comment)->post();
```


### delete()

Sends a `DELETE` request to Graph and returns the response in the form of a collection. Will throw an `FacebookQueryBuilderException` if something went wrong while trying to communicate with Graph.

```php
// Delete a comment
$response = $fqb->object('comment_id')->delete();

// Unlike a photo
$response = $fqb->object('photo_id/likes')->delete();
```


### edge(**string** "edge_name")

Returns an `Edge` value object to be passed to the `fields()` method.


### fields(**array|string** "list of fields or edges")

Set the fields and edges for this `Edge`. The fields and edges can be passed as an array or list of arguments.

```php
$edge_one = $fqb->edge('my_edge')->fields('my_field', 'my_other_field');
$edge_two = $fqb->edge('my_edge')->fields(['field_one', 'field_two']);

$obj = $fqb->object('some_object')->fields('some_field', $edge_one, $edge_two)->get();
```


### limit(**int** "number of results to return")

Limit the number of results returned from Graph.

```php
$edge = $fqb->edge('some_list_edge')->limit(7);
```


### with(**array** "data for body or request or modifiers")

The array should be an associative array. The key should be the name of the field as defined by Facebook.

If used in conjunction with the `post()` or `delete()` methods, the data will be sent in the body of the request.

```php
// Post a new comment to a photo
$comment_data = ['message' => 'My new comment!'];

$response = $fqb->object('photo_id')->with($comment_data)->post('comments');

// Update an existing comment
$comment_data = ['message' => 'My updated comment.'];

$response = $fqb->object('comment_id')->with($comment_data)->post();
```

If used in conjunction with a `get()` request, the data will be appended in the URL either in the sub edge or root edge.

```php
// Get the large version of a page profile picture
$profile_picture = $fqb->edge('picture')->with(['type' => 'large']);
$page_info = $fqb->object('some_page_id')->fields('name', $profile_picture)->get();
```


### search(**string** "search query"[, **string** "type of object to search"])

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
$root_edge = $fqb->object('me')->fields('id', 'email');

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
$root_edge = $fqb->object('me')->fields('email', $photos);

echo $root_edge;
```

The above example will output:

    /me?fields=email,photos.limit(5).fields(id,source)


## Response Objects

All responses from Graph are returned as a collection object that has many useful methods for playing with the response data.

```php
$user = $fqb->object('me')->fields('email', 'photos')->get();

// Access properties like an array
$email = $user['email'];

// Get data as array
$user_array = $user->toArray();

// Get data as JSON string
$user_json = $user->toJson();

// Iterate through the values
foreach ($user['photos'] as $photo) {
    // . . .
}

// Morph the data with a closure
$user['photos']->each(function ($value) {
    $value->new_height = $value->height + 22;
});
```

Check out the [Collection class](https://github.com/SammyK/FacebookQueryBuilder/blob/master/src/Collection.php) for the full list of methods.


### GraphObject

The `GraphObject` collection represents any set of data Graph would consider an "object". This could be a user, page, photo, etc. When you request an object by ID from Graph, the response will be returned as a `GraphObject` collection.

```php
$my_graph_object = $fqb->object('object_id')->get();
```

`GraphObject`'s can also contain `GraphCollection`'s if you request an edge in the fields list.

```php
$my_graph_object = $fqb->object('object_id')->fields('id','likes')->get();

$my_graph_collection = $my_graph_object['likes'];
```


#### Dates & Times

All datetime types on a `GraphObject` will be automatically cast as `Carbon` objects making datetime formatting/manipulation super easy.

```php
$photo = $fqb->object('some_photo_id')->fields('name', 'created_time')->get();

echo 'Photo added ' . $photo['created_time']->diffForHumans();
```

The above example will output:

    Photo added 4 days ago

[Learn more about Carbon.](https://github.com/briannesbitt/Carbon)


### GraphCollection

The `GraphCollection` collection is a collection of `GraphObject`'s.

```php
$my_graph_collection = $fqb->object('me/statuses')->get();
```


### GraphError

If Graph returns an error, the response will be cast as a `GraphError` collection and a `FacebookQueryBuilderException` will be thrown.

```php
try
{
    $statuses = $fqb->object('me/statuses')->limit(10)->get();
}
catch (FacebookQueryBuilderException $e)
{
    $graph_error = $e->getResponse();

    echo 'Oops! Graph said: ' . $graph_error['message'];
}
```


## Overwriting Persistent Storage

If you try to use the `getLoginUrl()` or `getTokenFromRedirect()` and you're getting a `FacebookSDKException` with the following error:

    Session not active, could not store state.

Then you've fallen victim to the annoying persistent data storage issue that [hasn't been fixed yet](https://github.com/facebook/facebook-php-sdk-v4/pull/44). So you'll need to implement your own class that overwrites the persistent storage found in the `\Facebook\FacebookRedirectLoginHelper`.

For example a Laravel implementation would look like the following.

```php
class LaravelFacebookRedirectLoginHelper extends \Facebook\FacebookRedirectLoginHelper
{
    protected function storeState($state)
    {
        Session::put('state', $state);
    }

    protected function loadState()
    {
        return $this->state = Session::get('state');
    }
}

FQB::setRedirectHelperAlias('LaravelFacebookRedirectLoginHelper');
```


## Testing

Just run `phpunit` from the root directory of this project.

``` bash
$ phpunit
```


## TODO

Future developments *(updated Sept 5, 2014)*

1. Upgrade to use the Facebook SDK v4.1 (when it comes out). This will give us batch request support & pagination on `GraphList` objects (response collections from the SDK).
2. Move away from using statics. Usage of statics will be deprecated in 2.0.
3. Use native collections (`\Facebook\GraphObject`, `\Facebook\GraphList`, etc) and entities (`\Facebook\Entities\AccessToken`, `\Facebook\Entities\SignedRequest`, etc) from the Facebook SDK v4.1 when it comes out.


## Contributing

Please see [CONTRIBUTING](https://github.com/SammyK/FacebookQueryBuilder/blob/master/CONTRIBUTING.md) for details.


## CHANGELOG

Please see [CHANGELOG](https://github.com/SammyK/FacebookQueryBuilder/blob/master/CHANGELOG.md) for history.


## Credits

- [Sammy Kaye Powers](https://github.com/SammyK)
- [All Contributors](https://github.com/SammyK/FacebookQueryBuilder/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE) for more information.
