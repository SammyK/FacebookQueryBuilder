# Facebook Query Builder

[![Build Status](http://img.shields.io/travis/SammyK/FacebookQueryBuilder.svg)](https://travis-ci.org/SammyK/FacebookQueryBuilder)
[![Latest Stable Version](http://img.shields.io/badge/Development%20Version-2.0.0-orange.svg)](https://packagist.org/packages/sammyk/facebook-query-builder)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE)


An elegant and efficient way to interface with Facebook's [Graph API](https://developers.facebook.com/docs/graph-api) using the latest [Facebook PHP SDK v4.1](https://github.com/facebook/facebook-php-sdk-v4). It's as easy as:

```php
$response = $fqb->node('me')->get();
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
- [Contributing](#contributing)
- [CHANGELOG](#changelog)
- [Credits](#credits)
- [License](#license)


## Installation

Facebook Query Builder is installed using [Composer](https://getcomposer.org/). Add the Facebook Query Builder package to your `composer.json` file.

```json
{
    "require": {
        "sammyk/facebook-query-builder": "~2.0"
    }
}
```

Or via the command line in the root of your project installation.

```bash
$ composer require "sammyk/facebook-query-builder:~2.0"
```


## Usage

After [creating an app in Facebook](https://developers.facebook.com/apps), you'll need to provide the app ID and secret.

```php
use SammyK\FacebookQueryBuilder\FQB;

$fqb = new FQB([
    'app_id' => '{app-id}',
    'app_secret' => '{app-secret}',
    ]);
```

Since `FQB` is just a decorator to the Facebook PHP SDK's `Facebook\Facebook` class, all the options available in the `Facebook\Facebook` class are also available in `FQB`.

For example:

```php
$fqb = new FQB([
    // . . .
    'enable_beta_mode' => true,
    'http_client_handler' => 'guzzle',
    ]);
```


## Obtaining An Access Token

Most calls to Graph require an access token. There are three ways to obtain an access token.

As of version 2.0 of the Facebook Query Builder, the `AccessToken` object was ported to the official Facebook PHP SDK v4.1. So you can just obtain an access token from the SDK methods.


### From A Redirect

The most common way to obtain an access token is to provide a login URL and get the access token on the specified callback URL.

```php
use SammyK\FacebookQueryBuilder\FQB;
use Facebook\Helpers\FacebookRedirectLoginHelper;

$fqb = new FQB([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  ]);
$facebookApp = $fqb->getApp();

$redirectHelper = new FacebookRedirectLoginHelper($facebookApp);

$loginUrl = $redirectHelper->getLoginUrl('http://my-callback/url');
```

You can optionally send in an array of permissions to request.

```php
$scope = ['email', 'user_status'];
$login_url = $redirectHelper->getLoginUrl('http://my-callback/url', $scope);
```

Then in the callback URL you can obtain the access token.

```php
use SammyK\FacebookQueryBuilder\FQB;
use Facebook\Helpers\FacebookRedirectLoginHelper;
use Facebook\Exceptions\FacebookSDKException;

$fqb = new FQB([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  ]);
$facebookApp = $fqb->getApp();
$facebookClient = $fqb->getClient();

$redirectHelper = new FacebookRedirectLoginHelper($facebookApp);

try {
  $accessToken = $redirectHelper->getAccessToken($facebookClient, 'http://my-callback/url');
} catch (FacebookQueryBuilderException $e) {
  // Failed to obtain access token
  echo 'Error:' . $e->getMessage();
}
```

See a full example of [obtaining an access token from redirect](https://github.com/SammyK/FacebookQueryBuilder/blob/master/examples/get_access_token_from_redirect.php).


### From Within App Canvas

If you are running your app from within the context of an app canvas, you can try to obtain an access token from the signed request that Facebook sends to your app.

```php
use SammyK\FacebookQueryBuilder\FQB;
use Facebook\Helpers\FacebookCanvasHelper;
use Facebook\Exceptions\FacebookSDKException;

$fqb = new FQB([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  ]);
$facebookApp = $fqb->getApp();
$facebookClient = $fqb->getClient();

try {
  $canvasHelper = new FacebookCanvasHelper($facebookApp);
  $accessToken = $canvasHelper->getAccessToken($facebookClient);
} catch(FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
}

if (isset($accessToken)) {
  // Logged in.
}
```


### From The Javascript SDK

If you are using the Javascript SDK on your site, FQB can obtain an access token from the signed request that the Javascript SDK sets in the cookie.

```php
use SammyK\FacebookQueryBuilder\FQB;
use Facebook\Helpers\FacebookJavaScriptHelper;
use Facebook\Exceptions\FacebookSDKException;

$fqb = new FQB([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  ]);
$facebookApp = $fqb->getApp();
$facebookClient = $fqb->getClient();

try {
  $jsHelper = new FacebookJavaScriptHelper($facebookApp);
  $accessToken = $jsHelper->getAccessToken($facebookClient);
} catch(FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
}

if (isset($accessToken)) {
  // Logged in.
}
```


## The AccessToken Object

By default access tokens will last for about 2 hours. You can exchange them for longer-lived tokens that last for about 60 days.

As of version 2.0 of the Facebook Query Builder, the `AccessToken` object was ported to the official Facebook PHP SDK v4.1. Refer to the [official documentation](#) for full details.


## Examples


### Getting a single object from Graph

Get the logged in user's profile.

```php
$user = $fqb->node('me')->fields('id','email')->get();
```

Get info from a Facebook page.

```php
$page = $fqb->node('facebook_page_id')->fields('id','name','about')->get();
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


### node(*string* "graph_node")

Returns a new instance of the `FQB` factory. Any valid Graph node can be passed to `node()`.


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


### GraphCollection

The `GraphCollection` collection is a collection of `GraphObject`'s.

```php
$my_graph_collection = $fqb->object('me/statuses')->get();
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


## Contributing

Please see [CONTRIBUTING](https://github.com/SammyK/FacebookQueryBuilder/blob/master/CONTRIBUTING.md) for details.


## CHANGELOG

Please see [CHANGELOG](https://github.com/SammyK/FacebookQueryBuilder/blob/master/CHANGELOG.md) for history.


## Credits

- [Sammy Kaye Powers](https://github.com/SammyK)
- [All Contributors](https://github.com/SammyK/FacebookQueryBuilder/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE) for more information.
