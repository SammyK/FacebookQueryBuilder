# CHANGELOG


## 2.0.0 - May 8, 2015

The upgrade to v2 is a complete pivot of the Facebook Query Builder. The `FQB` object is completely stripped down and only generates nested request syntax URL's and nothing else.

If you're trying to upgrade from Facebook Query Builder v1 to v2, it might be better to [familiarize yourself with the new API](https://github.com/SammyK/FacebookQueryBuilder/tree/master#facebook-query-builder) and how it is used with a 3rd party HTTP client to make requests to the Graph API.

- Removed all 3rd-party dependencies.
- Removed all HTTP client related code.
- Renamed `Edge` to `GraphEdge`.
- Renamed `RootEdge` to `GraphNode`.
- `GraphEdge` is now a child of `GraphNode` instead of the other way around. I must have been drunk.
- Removed all the classes that were ported over to the official Facebook PHP SDK v4.1:
    - `AccessToken`
    - `BaseGraphObject` & `GraphObjectInitializer` (ported over as `GraphObjectFactory`)
    - `Collection` (ported with limited functionality)
    - `GraphCollection` (ported over as `GraphList`)
    - `GraphObject` (ported into existing `GraphObject`)
    - `Response` (ported into existing `FacebookResponse`)
- Removed the supporting classes that were used to integrate into the Facebook PHP SDK v4.0
    - `ArrayHelpers`
    - `Auth`
    - `Connection`
    - `FacebookRequestMaker`
    - `GraphError`
    - `FacebookQueryBuilderException`
- Removed `FQB::setAppCredentials()` in favor of setting the `app_id` and `app_secret` via the constructor.
- Removed `FQB::setAccessToken()` in favor of setting the `default_access_token` via the constructor.
- Removed `FQB::setFacebookSession()` since FQB doesn't authenticate anything anymore.
- Removed `FQB::auth()` since FQB doesn't authenticate anything anymore.
- Removed `FQB::getConnection()` & `FQB::setConnection()` since FQB is no longer dependant on the PHP SDK.
- Added `FQB::modifiers()` and `GraphEdge::modifiers()` to replace what `FQB::with()` used to do so that the nomenclature makes more sense.
- Added `FQB::accessToken()` to overwrite the `default_access_token`.
- Renamed `GraphEdge::compileEdge()` and `GraphNode::compileEdge()` to `asUrl()`.
- Renamed `FQB::getQueryUrl()` to `asUrl()`.
- Fixed bug that put modifiers after the fields list that caused the following error from Graph: `Syntax error "Expected end of string instead of "."." at character n`


## 1.1.3 - November 9, 2014

- Updated version number of Facebook PHP SDK dependency so that it would not install v4.1 when `minimum-stability` is set to `dev`.


## 1.1.2 - November 5, 2014

- Moved latest stable to 1.1 branch.


## 1.1.1 - September 5, 2014

- Fixed bug that would sometimes reset the `date_default_timezone_set()`.
- Fixed bug where calling `compileEdge()` more than once on the same instance would cause unexpected results.
- Broadened the scope of catching exceptions from the Facebook PHP SDK.
- Updated the field expansion syntax that was [released in Graph version 2.1](https://developers.facebook.com/docs/graph-api/using-graph-api/v2.1#fieldexpansion).


## 1.1.0 - July 16, 2014

- Adjusted tagging to work according to [semver](http://semver.org/).


## 1.0.7 - June 10, 2014

- Added `search()` method to easily search Graph.
- Expanded the `with()` method to send modifiers on GET requests.
- Refactored how data from Graph is cast as collection objects to handle even more response variations.


## 1.0.6 - June 6, 2014

- Added ability to alias a custom `\Facebook\FacebookRedirectLoginHelper` with `FQB::setRedirectHelperAlias('MyCustomClass')`.


## 1.0.5 - June 2, 2014

- Added support for the scope array in `getLoginUrl()`.


## 1.0.4 - May 30, 2014

- Added `AccessToken` objects that do cool stuffs.
- Better support of Graph responses.
- Even better exception handling.
- Tweaked the examples for clarity.


## 1.0.3 - May 30, 2014

- Added feature to obtain an access token via redirect, canvas or Javascript.
- Added a setter to inject an existing `FacebookSession` object.
- Better exception handling.
- Beefed up the examples.


## 1.0.2 - May 29, 2014

- Changed SDK dependency to latest stable instead of dev-master.


## 1.0.1 - May 8, 2014

- Added tasty examples.
- Added `Carbon` support for more datetime types.


## 1.0.0 - May 7, 2014

- Initial release. Hello world!
