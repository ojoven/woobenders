<?php
require_once "settings/SettingsFilmDisaffinity.php";

class FilmDisaffinityCron {

    protected $cb;
    protected $pathToApp;

    public function initialize() {

        Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
        $this->cb = Codebird::getInstance();
        $this->cb->setToken(OAUTH_TOKEN, OAUTH_SECRET);

        // Path to App
        $this->pathToApp = ROOT_PATH . "app/bots/FilmDisaffinityApp/";
    }

    public function run() {

        $this->initialize();

        // Let's generate the screenshot
        $this->_generateScreenshot();


        // Let's tweet it


    }

    private function _generateScreenshot() {

        $pathToPhantomJs = $this->pathToApp . "phantomreview.js";

        // Generate screenshot plagiarized
        $url = "http://filmaffinity.ojoven.es/randombadreview/";
        exec("phantomjs --ssl-protocol=any " . $pathToPhantomJs .  " " . $url . " " . $this->pathToApp . "tmp/review.png png");

    }

}


