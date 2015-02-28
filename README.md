WooBenders
==============
![WooBendersLogo](http://ojoven.es/wp-content/uploads/2014/11/woobenders1.png "WooBenders Logo")

A framework to easily create Twitter bots.

(CAUTION! THE DOCS ARE NOT UPDATED, PLEASE WAIT SOME TIME BEFORE CLONING THIS REPO)

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
    // update here DB credentials, the Twitter keys and tokens and your bot's @screenname


Enjoy
------------------
    *app/consumer.php*
    // Just customize the _doMagic() function here to reply to the mentions

Run
------------------
To run, just execute ./run (with nohup if you want it to have it running after closing bash)

    nohup ./run > nohup.out 2>&1 &

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
Along with this framework, it comes an example of use. Just say hi! (in spanish), to [@donpepitobot](http://twitter.com/donpepitobot):

[Hola, @donpepitobot](https://twitter.com/intent/tweet?text=Hola,+@donpepitobot)
