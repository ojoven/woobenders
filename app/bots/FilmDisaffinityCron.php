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
        $this->_sendTweetScreenshot();

    }

    private function _generateScreenshot() {

        $pathToPhantomJs = $this->pathToApp . "phantomreview.js";
        $pathToScreenshot = $this->pathToApp . "tmp/review.png";

        // Generate screenshot plagiarized
        $url = "http://filmaffinity.ojoven.es/randombadreview/";
        $command = "/usr/local/bin/phantomjs " . $pathToPhantomJs .  " " . $url . " " . $pathToScreenshot . " png 2>&1";
        $return = shell_exec($command);

        echo $return . PHP_EOL . $command . PHP_EOL . date("Y-m-d H:i:s") . PHP_EOL;

    }

    private function _sendTweetScreenshot() {

        $pathToScreenshot = $this->pathToApp . "tmp/review.png";
        if (file_exists($pathToScreenshot)) {

            $reply = $this->cb->media_upload(array('media' => $pathToScreenshot));

            $params = array('status' => "");

            // Any photos to upload?
            if ($reply->media_id_string) {
                $params['media_ids'] = $reply->media_id_string;
            }

            $reply = $this->cb->statuses_update($params);

            // Remove screenshot
            unlink($pathToScreenshot);

        }

    }

}


