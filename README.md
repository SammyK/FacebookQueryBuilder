# Facebook Query Builder

[![Build Status](http://img.shields.io/travis/SammyK/FacebookQueryBuilder/master.svg)](https://travis-ci.org/SammyK/FacebookQueryBuilder)
[![Latest Stable Version](http://img.shields.io/badge/Latest%20Stable-2.0.0-blue.svg)](https://packagist.org/packages/sammyk/facebook-query-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/sammyk/facebook-query-builder.svg)](https://packagist.org/packages/sammyk/facebook-query-builder)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE)


A query builder that makes it easy to create complex & efficient [nested requests](https://developers.facebook.com/docs/graph-api/using-graph-api#fieldexpansion) to Facebook's [Graph API](https://developers.facebook.com/docs/graph-api) to get [lots of specific data back](https://www.sammyk.me/optimizing-request-queries-to-the-facebook-graph-api) with one request.

Facebook Query Builder has no production dependencies.

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB;

$photosEdge = $fqb->edge('photos')->fields(['id', 'source'])->limit(5);
$request = $fqb->node('me')->fields(['id', 'email', $photosEdge]);

echo (string) $request;
# https://graph.facebook.com/me?fields=id,email,photos.limit(5){id,source}
```

- [Introduction](#introduction)
- [Installation](#installation)
- [Usage](#usage)
    - [Initialization](#initialization)
    - [A basic example](#a-basic-example)
    - [Get data across multiple edges](#get-data-across-multiple-edges)
- [Sending the nested request](#sending-the-nested-request)
- [Obtaining an access token](#obtaining-an-access-token)
- [Configuration Settings](#configuration-settings)
- [Method Reference](#method-reference)
- [Handling the response](#handling-the-response)
- [Contributing](#contributing)
- [License](#license)
- [Security](#security)


## Introduction

The Facebook Query Builder uses the same [Graph API nomenclature](https://developers.facebook.com/docs/graph-api/quickstart#basics) for three main concepts:

1. **Node:** A node represents a "real-world thing" on Facebook like a user or a page.
2. **Edge:** An edge is the relationship between two or more nodes. For example a "photo" node would have a "comments" edge.
3. **Field:** Nodes have properties associated with them. These properties are called fields. A user has an "id" and "name" field for example.

When you send a request to the Graph API, the URL is structured like so:

    https://graph.facebook.com/node-id/edge-name?fields=field-name

To generate the same URL with Facebook Query Builder, you'd do the following:

```php
$edge = $fqb->edge('edge-name')->fields('field-name');
echo $fqb->node('node-id')->fields($edge);
```

If you were to execute that script, you might be surprised to see the URL looks a little different because it would output:

    https://graph.facebook.com/node-id?fields=edge-name{field-name}

The two URL's are functionally identical with the exception of how the Graph API returns the response data. What makes the URL generated with Facebook Query Builder different is that it is being expressed as a [nested request](https://developers.facebook.com/docs/graph-api/using-graph-api#fieldexpansion).

And that is what makes Facebook Query Builder so powerful. It does the heavy lifting to generate properly formatted nested requests from a fluent, easy-to-read PHP interface.


## Installation

Facebook Query Builder is installed using [Composer](https://getcomposer.org/). Add the Facebook Query Builder package to your `composer.json` file.

```json
{
    "require": {
        "sammyk/facebook-query-builder": "~2.0"
    }
}
```


## Usage

### Initialization

To start interfacing with Facebook Query Builder you simply instantiate a `FQB` object.

```php
// Assuming you've included your composer autoload.php file before this line.
$fqb = new SammyK\FacebookQueryBuilder\FQB;
```

There are a number of [configuration options](#configuration-settings) you can pass to the `FQB` constructor.


### A basic example

Below is a basic example that gets the logged in user's `id` & `email` (assuming the user granted your app [the `email` permission](https://developers.facebook.com/docs/facebook-login/permissions/v2.3#reference-email)).

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB;

$request = $fqb->node('me')
               ->fields(['id', 'email'])
               ->accessToken('user-access-token')
               ->graphVersion('v2.3');

$response = file_get_contents((string) $request);

var_dump($response);
# string(50) "{"id":"12345678","email":"foo-bar\u0040gmail.com"}"
```


### Get data across multiple edges

The bread and butter of the Facebook Query Builder is its support for [nested requests](https://developers.facebook.com/docs/graph-api/using-graph-api/v2.0#fieldexpansion). Nested requests allow you to get a lot of data from the Graph API with just one request.

The following example will get the logged in user's name & first 5 photos they are tagged in with just one call to Graph.

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([/* . . . */]);

$photosEdge = $fqb->edge('photos')->fields(['id', 'source'])->limit(5);
$request = $fqb->node('me')->fields(['name', $photosEdge]);

// Assumes you've set a default access token
$response = file_get_contents((string) $request);

var_dump($response);
# string(1699) "{"name":"Sammy Kaye Powers","photos":{"data":[{"id":"123","source":"https:\/\/scontent.xx.fbcdn.net\/hphotos-xfp1 . . .
```

And edges can have other edges embedded in them to allow for infinite deepness. This allows you to do fairly complex calls to Graph while maintaining very readable code.

The following example will get user `1234`'s name, and first 10 photos they are tagged in. For each photo it gets the first 2 comments and all the likes.

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([/* . . . */]);

$likesEdge = $fqb->edge('likes');
$commentsEdge = $fqb->edge('comments')->fields('message')->limit(2);
$photosEdge = $fqb->edge('photos')
                  ->fields(['id', 'source', $commentsEdge, $likesEdge])
                  ->limit(10);

$request = $fqb->node('1234')->fields(['name', $photosEdge]);

// Assumes you've set a default access token
$response = file_get_contents((string) $request);

var_dump($response);
# string(10780) "{"name":"Some Foo User","photos":{"data":[ . . .
```


## Sending the nested request

Since Facebook Query Builder is just a tool that generates nested request syntax, it doesn't make any requests to the Graph API for you. You'll have to use some sort of HTTP client to send the requests.

We'll assume you've already [created an app in Facebook](https://developers.facebook.com/apps) and have [obtained an access token](https://developers.facebook.com/docs/facebook-login/login-flow-for-web).


### Requests with The Facebook PHP SDK

The recommended way to send requests & receive responses is to use the official [Facebook PHP SDK v5](https://github.com/facebook/facebook-php-sdk-v4/tree/master). You'll need to create an instance of the `Facebook\Facebook` super service class from the native Facebook PHP SDK.

```php
$fb = new Facebook\Facebook([
    'app_id' => 'your-app-id',
    'app_secret' => 'your-app-secret',
    'default_graph_version' => 'v2.3',
    ]);
$fqb = new SammyK\FacebookQueryBuilder\FQB;

$fb->setDefaultAccessToken('my-access-token');

$request = $fqb->node('me')->fields(['id', 'name', 'email']);

try {
    $response = $fb->get($request->asEndpoint());
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo $e->getMessage();
    exit;
}

var_dump($response->getDecodedBody());
```

You'll noticed we're using the [`asEndpoint()` method](#asendpoint) to send the generated request to the SDK. That's because the SDK will automatically prefix the URL with the Graph API hostname. The `asEndpoint()` method will return an un-prefixed version of the URL.

The official Facebook PHP SDK will automatically add the Graph API version, the app secret proof, and the access token to the URL for you, so you don't have to worry about setting those options on the `FQB` object.


### Requests with native PHP

As you've already seen in the basic examples above, you can simply use PHP's flexible [`file_get_contents()`](http://php.net/file_get_contents) to send the requests to the Graph API. Just be sure to set your Graph API version prefix with `default_graph_version` & set your app secret with `app_secret` to ensure [all the requests get signed with an app secret proof](#enabling-app-secret-proof).

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([
    'default_graph_version' => 'v2.3',
    'app_secret'            => 'your-app-secret',
]);

// Grab Mark Zuckerberg's public info
$request = $fqb->node('4')->accessToken('my-access-token');

$response = file_get_contents((string) $request);

var_dump($response);
```

For more info about handling the response, check out [responses with native PHP](#responses-with-native-php) below.


## Obtaining an access token

As the Facebook Query Builder is exclusive to building nested request syntax, it cannot be used directly to obtain an access token.

The Facebook login process uses [OAuth 2.0](http://oauth.net/2/) behind the scenes. So you can use any OAuth 2.0 client library to obtain a user access token from Facebook. Here are a few recommendations:

- The official [Facebook PHP SDK v5](https://github.com/facebook/facebook-php-sdk-v4/tree/master) **(recommended)**
- The PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client) and the corresponding [Facebook Provider](https://github.com/thephpleague/oauth2-facebook)
- Laravel 5's [Socialite](http://laravel.com/docs/5.0/authentication#social-authentication) library


## Configuration settings

A number of configuration settings can be set via the `FQB` constructor.

- The [`default_access_token` option](#setting-the-access-token) lets you define a default fallback access token for all generated queries.
- The [`default_graph_version` option](#setting-the-graph-version) lets you define the default [Graph API version](https://developers.facebook.com/docs/apps/versions) URL prefix for all generated queries.
- An important security layer to the Graph API is the [app secret proof](https://developers.facebook.com/docs/graph-api/securing-requests#appsecret_proof) which should be enabled on your app by default. You can sign each request generated by Facebook Query Builder with an app secret proof by setting the [`app_secret` option](#enabling-app-secret-proof) with your app secret.

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([
    'default_access_token'  => 'your-access-token',
    'default_graph_version' => 'v2.3',
    'app_secret'            => 'your-app-secret',
]);
```


### Setting the access token

If you're using the Facebook PHP SDK and have set a default access token for the SDK to use, then you won't need to worry about appending the access token to the request.

If you're using some other HTTP client, you can set the default fallback access token when you instantiate the `FQB` service with the `default_access_token` option or you can append the access token to the nested request using the `accessToken()` method.

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([
    'default_access_token' => 'fallback_access_token',
]);

$request = $fqb->node('me');
echo $request->asEndpoint();
# /me?access_token=fallback_access_token

$request = $fqb->node('me')->accessToken('bar_token');
echo $request->asEndpoint();
# /me?access_token=bar_token
```


### Setting the Graph version

It's important that you set the [version of the Graph API](https://developers.facebook.com/docs/apps/versions) that you want to use since the Graph API is subject to a [breaking change schedule](https://developers.facebook.com/docs/apps/changelog).

If you're using the Facebook PHP SDK and have set a default Graph version for the SDK to use, then you won't need to worry about setting the Graph version in the Facebook Query Builder.
 
If you're using some other HTTP client, you can set the default fallback Graph version when you instantiate the `FQB` service with the `default_graph_version` option or you can set it on a per-request basis using the `graphVersion()` method.

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([
    'default_graph_version' => 'v2.3',
]);

$request = $fqb->node('me');
echo $request->asEndpoint();
# /v2.3/me

$request = $fqb->node('me')->graphVersion('v1.0');
echo $request->asEndpoint();
# /v1.0/me
```

PS: [Graph v1.0 is dead](https://developers.facebook.com/docs/apps/api-v1-deprecation). :skull:


### Enabling app secret proof

As an added security feature, you can [sign each request to the Graph API with an `app_secretproof`](https://developers.facebook.com/docs/graph-api/securing-requests). It is highly recommended that you [edit your app settings to require the app secret proof](https://developers.facebook.com/docs/graph-api/securing-requests#require-proof) for all requests.

If you're using the Facebook PHP SDK to send requests to the Graph API, it will automatically append the app secret proof for you.

If you're using some other HTTP client, the app secret proof will be generated for a request if both an access token and app secret are set for a request. You can set the app secret when you instantiate the `FQB` service using the `app_secret` option.

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([
    'app_secret' => 'foo_secret',
]);

$request = $fqb->node('me')->accessToken('bar_token');
echo $request->asEndpoint();
# /me?access_token=bar_token&appsecret_proof=2ceec40b7b9fd7d38fff1767b766bcc6b1f9feb378febac4612c156e6a8354bd
```


### Enabling the beta version of Graph

Before changes to the Graph API are rolled out to production, they are deployed to the [beta tier](https://developers.facebook.com/docs/apps/beta-tier) first.

By default, when you generate a nested request, it will be prefixed with the production hostname for the Graph API which is [https://graph.facebook.com/](https://graph.facebook.com/).

```php
echo (string) $fqb->node('4');
# https://graph.facebook.com/4
```

To enable the beta tier for the requests generated by `FQB`, set the `enable_beta_mode` option to `true`. Once enabled, all generated URL's will be prefixed with the beta hostname of the Graph API which is [https://graph.beta.facebook.com/](https://graph.beta.facebook.com/).

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([
    'enable_beta_mode' => true,
]);

echo (string) $fqb->node('4');
# https://graph.beta.facebook.com/4
```


## Method reference

- [node()](#node)
- [edge()](#edge)
- [fields()](#fields)
- [modifiers()](#modifiers)
- [limit()](#limit)
- [accessToken()](#accesstoken)
- [graphVersion()](#graphversion)
- [asUrl()](#asurl)
- [asEndpoint()](#asendpoint)


### node()

```php
node(string $graphNodeName): FQB
```

Returns a new mutable instance of the `FQB` entity. Any valid Graph node or endpoint on the Graph API can be passed to `node()`.

```php
$userNode = $fqb->node('me');
```


### edge()

```php
node(string $edgeName): GraphEdge
```

Returns an mutable instance of `GraphEdge` entity to be passed to the `FQB::fields()` method.

```php
$photosEdge = $fqb->edge('photos');
```


### fields()

```php
fields(mixed $fieldNameOrEdge[, mixed $fieldNameOrEdge[, ...]]): FQB
```

Set the fields and edges for a `GraphNode` or `GraphEdge` entity. The fields and edges can be passed as an array or list of arguments.

```php
$edge = $fqb->edge('some_edge')->fields(['field_one', 'field_two']);
$node = $fqb->node('some_node')->fields('my_field', 'my_other_field', $edge);
```


### modifiers()

```php
modifiers(array $modifiers): FQB
```

Some endpoints of the Graph API support additional parameters called "modifiers".

An example endpoint that supports modifiers is the [`/{object-id}/comments` edge](https://developers.facebook.com/docs/graph-api/reference/v2.3/object/comments#readmodifiers).

```php
// Order the comments in chronological order
$commentsEdge = $fqb->edge('comments')->modifiers(['filter' => 'stream']);
$request = $fqb->node('1044180305609983')->fields('name', $commentsEdge);
```


### limit()

```php
limit(int $numberOfResultsToReturn): FQB
```

You can specify the number of results the Graph API should return from an edge with the `limit()` method.

```php
$edge = $fqb->edge('photos')->limit(7);
```

Since the "limit" functionality is just a [modifier](#modifiers) in the Graph API, the `limit()` method is a convenience method for sending the `limit` param to the `modifiers()` method. So the same functionality could be expressed as:

```php
$edge = $fqb->edge('photos')->modifiers(['limit' => 7]);
```


### accessToken()

```php
accessToken(string $accessToken): FQB
```

You can set the access token for a specific request with the `accessToken()` method.

```php
$request = $fqb->node('BradfordWhelanPhotography')->accessToken('foo-token');

echo $request->asEndpoint();
# /BradfordWhelanPhotography?access_token=foo-token
```


### graphVersion()

```php
graphVersion(string $graphApiVersion): FQB
```

You can set the Graph version URL prefix for a specific request with the `graphVersion()` method.

```php
$request = $fqb->node('me')->graphVersion('v2.3');

echo $request->asEndpoint();
# /v2.3/me
```


### asUrl()

```php
asUrl(): string
```

You can obtain the generated request as a full URL using the `asUrl()` method.

```php
$request = $fqb->node('me');

echo $request->asUrl();
# https://graph.facebook.com/me
```

The magic method `__toString()` is a alias to the `asUrl()` method so casting the `FQB` instance as a string will perform the same action.

```php
$request = $fqb->node('me');

echo (string) $request;
# https://graph.facebook.com/me
```


### asEndpoint()

```php
asEndpoint(): string
```

The `asEndpoint()` is identical to the `asUrl()` method except that it will return the URL without the Graph API hostname prefixed to it.

```php
$request = $fqb->node('me');

echo $request->asEndpoint();
# /me
```

This is particularly handy for working with the official Facebook PHP SDK which will automatically prefix the Graph hostname to the URL for you.


## Handling the response

Responses will vary depending on what HTTP client you're using to interface with the Graph API.


### Responses with the Facebook PHP SDK

All responses from the `get()`, `post()`, and `delete()` methods return a `Facebook\FacebookResponse` from the native Facebook PHP SDK.

```php
$fb = new Facebook\Facebook([/* . . . */]);
$fqb = new SammyK\FacebookQueryBuilder\FQB([/* . . . */]);

$fb->setDefaultAccessToken('my-access-token');

$request = $fqb->node('me')->fields(['email', 'photos']);

try {
    $response = $fb->get($request->asEndpoint());
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo $e->getMessage();
    exit;
}

$userNode = $response->getGraphUser();

// Access properties like an array
$email = $userNode['email'];

// Get data as array
$userNodeAsArray = $userNode->asArray();

// Get data as JSON string
$userNodeAsJson = $userNode->asJson();

// Iterate over the /photos edge
foreach ($userNode['photos'] as $photo) {
    // . . .
}

// Morph the data with a closure
$userNode['photos']->each(function ($value) {
    $value->new_height = $value->height + 22;
});
```

See the official documentation for more information on the [`FacebookResponse` entity](https://github.com/facebook/facebook-php-sdk-v4/blob/master/docs/FacebookResponse.fbmd).


### Responses with native PHP

If you're using `file_get_contents()` to send requests to the Graph API, the responses will be a string of JSON from the Graph API. You can decode the JSON responses to a plain-old PHP array.

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([/* . . . */]);

$request = $fqb->node('4')
               ->fields(['id', 'name'])
               ->accessToken('user-access-token');

$response = file_get_contents((string) $request);

$data = json_decode($response, true);

var_dump($data);
# array(2) { ["id"]=> string(1) "4" ["name"]=> string(15) "Mark Zuckerberg" }
```

If there was an error response from the Graph API, `file_get_contents()` will return `false`. You can obtain the response headers by examining the `$http_response_header` variable which gets set automatically by `file_get_contents()` to figure out what went wrong.

```php
$fqb = new SammyK\FacebookQueryBuilder\FQB([/* . . . */]);

$request = $fqb->node('Some-Invalid-Node')->accessToken('user-access-token');

$response = file_get_contents((string) $request);

if ($response === false) {
    var_dump($http_response_header);
    exit;
}

$data = json_decode($response, true);

var_dump($data);
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

The MIT License (MIT). Please see [License File](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE) for more information.## License


## Security

If you find a security exploit in this library, please [notify the project maintainer privately](mailto:me@sammyk.me) to get the issue resolved.
