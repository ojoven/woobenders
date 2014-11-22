WooBenders
==============

A mention / response Twitter bot framework.

Installation
------------------

    git clone https://github.com/ojoven/woobenders.git


* Create the bot account on Twitter

https://twitter.com/signup

* Create an app on Twitter

https://apps.twitter.com/app/new

(Remember to give it write permissions)

Configure
------------------
    *app/settings.php*
    // update here DB credentials, the Twitter keys and tokens and the your @bot's screen name


Enjoy
------------------
    *app/consumer.php*
    // Just customize the _doMagic() function here to reply to the mentions to your @bot as you wish

Credits
------------------

We're using the following libraries:

* [CodeBird](https://github.com/jublonet/codebird-php)
* [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class)
* [PhireHose](https://github.com/fennb/phirehose)
* [tmhOAuth](https://github.com/themattharris/tmhOAuth)

This framework has been done by [@ojoven](http://twitter.com/ojoven) and you can find me, too, at http://ojoven.es

Example
----------------
Along with this framework, it comes an example of use. Just say hi! (in spanish), to @donpepito:

[Hola, @donpepitobot](https://twitter.com/intent/tweet?text=Hola,+@donpepitobot)
