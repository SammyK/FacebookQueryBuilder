# CHANGELOG


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
