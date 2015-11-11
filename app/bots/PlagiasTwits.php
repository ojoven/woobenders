<?php

class PlagiasTwits implements ResponseBehaviour {

    // Auxiliar Functions for Response Behaviour
    use ResponseFunctions;

    protected $db; // database connection
    protected $cb; // codebird (auxiliar wrapper to post tweets)
    protected $type_streaming; // stream keywords or user
    protected $pathToApp;

    const PLAGIER_ID = "1495782590"; // ID for @escupotwits account
    const MIN_FAVS_ORIGINAL = 50;

    /** Initialize bot: DB, codebird... **/
    public function initialize() {

        // First, let's initialize the settings
        $this->initSettings();

        // Keywords track or user follow streaming
        $this->type_streaming = Consumer::TYPE_STREAMING_FOLLOW;

        // Database
        if (USE_DB) {
            $this->db = new MysqliDb(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_DATABASE_NAME);
        }

        // Codebird
        Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
        $this->cb = Codebird::getInstance();
        $this->cb->setToken(OAUTH_TOKEN, OAUTH_SECRET);

        // Path to App
        $this->pathToApp = ROOT_PATH . "app/bots/PlagiasTwitsApp/";

    }

    /** Return type of streaming **/
    public function getTypeStreaming() {
        return $this->type_streaming;
    }

    /** Returns the keywords the bot will stream for **/
    public function getKeywords() {

        $keywords = array();
        array_push($keywords, self::PLAGIER_ID);

        return $keywords;

    }

    /** Process the tweet retrieved by the stream **/
    public function processTweet($status) {

        // Get the array from the json
        $tweet = json_decode($status);

        if (isset($tweet->id_str)) {
            // We got the tweet

            // We can render some tweet options in console
            $this->renderTweetConsole($tweet);

            // We can save the tweet for further treatments (limits, stats, etc.)
            $this->saveTweetDb($tweet);

            // And here, the app's magic
            $this->plagiasTwitsMagic($tweet);

        } else {
            // We've missed some tweets, we may want to treat this exception
            // Though for this kind of bots this shouldn't happen
            $this->treatMissedTweets($tweet);
        }

    }

    /** Get the message for the mention and send tweet if applies **/
    public function plagiasTwitsMagic($plagiarizedTweet) {

        // First, we parse the tweet to retrieve just the important information
        $plagiarizedTweet = $this->parseTweet($plagiarizedTweet);

        if ($plagiarizedTweet['is_retweet']) return; // If it is a retweet, do nothing

        // Let's search for the original tweet
        $originalTweet = $this->findOriginalTweet($plagiarizedTweet);

        if ($originalTweet) {
            $message = $this->buildMessageToReplyToPlagiarism($plagiarizedTweet, $originalTweet);
            $mediaId = $this->getScreenshotTweets($plagiarizedTweet,$originalTweet);

            // Let's add some randome sleep time to prevent Twitter to treat us as a bot and show us in the tweet replies. Will this work?
            //$timeToWait = rand(8,20);
            $timeToWait = 1;
            sleep($timeToWait);
            $this->sendResponseTweet($message, $plagiarizedTweet['id'], $mediaId);
        }

    }

    public function getScreenshotTweets($plagiarizedTweet,$originalTweet) {

        $this->generateScreenshots($plagiarizedTweet,$originalTweet);
        $this->treatScreenshots();
        $mediaId = $this->uploadResults();
        $this->removeScreenshotTmpFiles();

        return $mediaId;
    }

    public function uploadResults() {

        $resultFile = $this->pathToApp . "tmp/result.png";
        if (file_exists($resultFile)) {
            $reply = $this->cb->media_upload(array('media' => $resultFile));
            return $reply->media_id_string;
        } else {
            return false;
        }

    }

    public function removeScreenshotTmpFiles() {

        unlink($this->pathToApp . "tmp/plagiarized.png");
        unlink($this->pathToApp . "tmp/original.png");
        unlink($this->pathToApp . "tmp/result.png");

    }

    public function generateScreenshots($plagiarizedTweet,$originalTweet) {

        $pathToPhantomJs = $this->pathToApp . "rendertweets.js";

        // Generate screenshot plagiarized
        $plagiarizedTweetUrl = "https://twitter.com/" . $plagiarizedTweet['user_screen_name'] . "/status/" . $plagiarizedTweet['id'];
        exec("phantomjs --ssl-protocol=any " . $pathToPhantomJs .  " " . $plagiarizedTweetUrl . " " . $this->pathToApp . "tmp/plagiarized.png png");

        // Generate screenshot original
        $originalTweetUrl = "https://twitter.com/" . $originalTweet['user_screen_name'] . "/status/" . $originalTweet['id'];
        exec("phantomjs --ssl-protocol=any " . $pathToPhantomJs .  " " . $originalTweetUrl . " " . $this->pathToApp . "tmp/original.png png");

    }

    public function treatScreenshots() {

        $tmpFolder = $plagiarizedFile = $this->pathToApp . "tmp/";
        $layersFolder = $plagiarizedFile = $this->pathToApp . "images/";
        $plagiarizedFile = $tmpFolder . "plagiarized.png";
        $originalFile = $tmpFolder . "original.png";

        if (file_exists($plagiarizedFile) && file_exists($originalFile)) {

            // Plagiarized
            $plagiarized = ImageWorkshop::initFromPath($plagiarizedFile);
            $plagiarized->resizeInPixel(600, null, true);

            $plagiarizeLayer = ImageWorkshop::initFromPath($layersFolder . 'layer_plagiarized.png');
            $plagiarizeLayer->resizeInPixel(500, null, true);
            $plagiarized->addLayerOnTop($plagiarizeLayer, 0, 0, 'MM');

            // Original
            $original = ImageWorkshop::initFromPath($originalFile);
            $original->resizeInPixel(600, null, true);

            $originalLayer = ImageWorkshop::initFromPath($layersFolder . 'layer_original.png');
            $originalLayer->resizeInPixel(80, null, true);
            $original->addLayerOnTop($originalLayer, 40, 40, 'RB');

            // Mix
            $firGroup1 = ImageWorkshop::initVirginLayer($plagiarized->getWidth(), $plagiarized->getHeight() + $original->getHeight());
            $firGroup1->addLayerOnTop($plagiarized, 0, 0, 'LT');
            $firGroup1->addLayerOnTop($original, 0, 0, 'LB');
            $firGroup1->save($tmpFolder, "result.png", true, null, 100);

        }

    }

    private function findOriginalTweet($plagiarizedTweet) {

        $possibleOriginalTweets = $this->searchTweets($plagiarizedTweet['text']);

        if (!empty($possibleOriginalTweets)) {

            // Instead of comparing dates, etc. we'll just retrieve the last tweet from this search
            $originalTweet = end($possibleOriginalTweets);
            if ($originalTweet['user_id']!=self::PLAGIER_ID && $originalTweet['favs']>self::MIN_FAVS_ORIGINAL) { // To be sure that is a plagiarism of a popular tweet
                return $originalTweet;
            }

        }

        return false;

    }

    /** Get the response message that belongs to that tweet mention **/
    private function buildMessageToReplyToPlagiarism($plagiarizedTweet, $originalTweet) {

        // Not to repeat always the same message
        $templates = array(
            "Oye, @TwitterEspanol, ¿hacemos algo con [plagier_screen_name] y sus plagios a [original_screen_name]? ☞ [original_tweet_url]",
            "Mira @TwitterSpain, otro plagio de [plagier_screen_name] a [original_screen_name] ☞ [original_tweet_url]",
            "¿No podéis hacer nada @TwitterEspanol para cerrar a [plagier_screen_name]? [original_screen_name] ☞ [original_tweet_url]",
            "¿Y si reportamos en masa a [plagier_screen_name] por plagier? Mirad ☞ [original_tweet_url] de [original_screen_name]",
            "¡PLAGIO! [plagier_screen_name] te has copiado este tuit de [original_screen_name] ☞ [original_tweet_url] cc/ @TwitterSpain",
            "¡Vaya copiada [plagier_screen_name]! El original es de [original_screen_name] ☞ [original_tweet_url] cc/ @TwitterSpain",
            "¡Eres un plagier [plagier_screen_name]! El original de esta copiada es de [original_screen_name]: [original_tweet_url]",
            "¿Otro plagio, [plagier_screen_name]? No paras, ¿eh? El tuit güeno es este de [original_screen_name] ☞ [original_tweet_url]",
            "¡Venga esas copiadas [plagier_screen_name]! El tuit original ☞ [original_tweet_url] es de [original_screen_name]",
            "✋ ¡STOP PLAGIOS [plagier_screen_name]! Tuit de [original_screen_name] ¡más faveable y retuiteable! [original_tweet_url]",
            "El original de esta copiada de [plagier_screen_name] es de [original_screen_name] ☞ [original_tweet_url] cc/ @TwitterEspanol",
            //"OLA KE ASE [plagier_screen_name] TU COPIA O KE ASE. El original, de [original_screen_name] ☞ [original_tweet_url]",
            "De bot a bot [plagier_screen_name], deja de copiar a [original_screen_name] tuits molones como este: [original_tweet_url]",
            //"Once again [plagier_screen_name] copied an original tweet from [original_screen_name]  ☞ [original_tweet_url]",
            //"Hola [original_screen_name], aviso de que [plagier_screen_name] te ha copiado este tuit: [original_tweet_url]",
            //"Qué hay, [original_screen_name], comentarte que [plagier_screen_name] te acaba de copiar este tuit: [original_tweet_url]",
            //"Hey, ya sabes, [original_screen_name], una nueva copiada de [plagier_screen_name] a tu tuit ☞ [original_tweet_url]",
            "Jo, [plagier_screen_name] tío ☹, otra copiada descarada a [original_screen_name] ☞ [original_tweet_url] cc/ @TwitterEspanol",
            "✌✌✌ ¡Otro plagio desenmascarado a [plagier_screen_name]! Tuit de [original_screen_name] ☞ [original_tweet_url]"
        );

        $message = $templates[array_rand($templates)];
        $parsedMessage = $this->parseVariablesTweet($message, $plagiarizedTweet, $originalTweet);
        echo $parsedMessage . PHP_EOL;

        return $parsedMessage;

    }

    private function parseVariablesTweet($message, $plagiarizedTweet, $originalTweet) {

        $message = str_replace("[plagier_screen_name]", "@" . $plagiarizedTweet['user_screen_name'], $message);
        $message = str_replace("[original_screen_name]", "@" . $originalTweet['user_screen_name'], $message);
        $originalTweetUrl = "https://twitter.com/" . $originalTweet['user_screen_name'] . "/status/" . $originalTweet['id'];
        $message = str_replace("[original_tweet_url]", $originalTweetUrl, $message);

        return $message;


    }

}