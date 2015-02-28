WooBenders
==============
![WooBendersLogo](http://ojoven.es/wp-content/uploads/2014/11/woobenders1.png "WooBenders Logo")

A framework to ease the creation of Twitter bots.


Installation
------------------

    git clone https://github.com/ojoven/woobenders.git


* Create the bot account on Twitter

https://twitter.com/signup

* Create an app on Twitter

https://apps.twitter.com/app/new

(Remember to give it write permissions)

Customize
------------------
You have to create your own bot as a PHP class on app/bots. Please check DonPepitoBot and PlagiasTwits as examples on what can be done.

For the bot to work you must create the settings configuration file by renaming

    app/bots/settings/Settings.php.default

to

    Settings[YourBotsClassName].php

and set there the Twitter keys and tokens, apart from the DB credentials in case you want to make use of a DB.

Oauth
------------------
If the bot's Twitter profile is the one from which you create the Twitter app on https://apps.twitter.com/app/new
you can get the OAuth Tokens directly in the App's settings page. If not, you'll need to call

    http://[pathToYourApp]/oauth.php

to give access to the profile that will be the bot, and copy paste the
token credentials on your Settings file.

Run
------------------
To run, just execute ./run [YourBotsName] (with nohup if you want it to have it running after closing bash)

    nohup ./run [YourBotsName] > nohup.out 2>&1 &

Credits
------------------

We're using the following libraries:

* [CodeBird](https://github.com/jublonet/codebird-php)
* [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class)
* [PhireHose](https://github.com/fennb/phirehose)
* [tmhOAuth](https://github.com/themattharris/tmhOAuth)

This framework has been done by [@ojoven](http://twitter.com/ojoven). You can find me, too, at http://ojoven.es

Example
----------------
Along with this framework, it comes a working example of use. Just say hi! (in spanish), to [@donpepitobot](http://twitter.com/donpepitobot):

[Hola, @donpepitobot](https://twitter.com/intent/tweet?text=Hola,+@donpepitobot)

Disclaimer
----------------
This is not intended to be an exceptional framework, but its intent is to ease the
