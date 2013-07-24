TwitterAPI
==========

OAuth and data wrapper over Twitter API v1.1, written in PHP

## Instructions

1. index.php is a sample script that uses TwitterAPI.inc.php
2. Create a new app (or use the credentials of an existing app) by visiting https://dev.twitter.com/apps
3. Get your app's consumer key and consumer secret. Update TwitterAPI.inc.php with it ($consumerKey and $consumerSecret).
4. Think of a random string as your nonce secret and update TwitterAPI.inc.php accordingly ($nonceSecret).
5. Host TwitterAPI.inc.php and index.php on a webserver in a directory named /twitter/.
6. Update the callback URL of your app (on Twitter) to http://yourDomainName.com/twitter/index.php
7. Hit http://yourDomainName.com/twitter/index.php - It will make you do the 3-legged OAuth dance with Twitter first.
8. Voila! After the OAuth dance, you will see beautiful JSON data transformed into PHP arrays and echoed in a webpage!
