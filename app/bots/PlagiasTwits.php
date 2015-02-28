<?php

class PlagiasTwits implements ResponseBehaviour {

    // Auxiliar Functions for Response Behaviour
    use ResponseFunctions;

    protected $db; // database connection
    protected $cb; // codebird (auxiliar wrapper to post tweets)
    protected $type_streaming; // stream keywords or user

    const PLAGIER_ID = "1286726142"; // @ojovenBizarro, for testing
    // const PLAGIER_ID = "1495782590"; // ID for @escupotwits account
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
    private function plagiasTwitsMagic($plagiarizedTweet) {

        // First, we parse the tweet to retrieve just the important information
        $plagiarizedTweet = $this->parseTweet($plagiarizedTweet);

        if ($plagiarizedTweet['is_retweet']) return; // If is a retweet, do nothing

        // Let's search for the original tweet
        $originalTweet = $this->findOriginalTweet($plagiarizedTweet);

        if ($originalTweet) {
            $message = $this->buildMessageToReplyToPlagiarism($plagiarizedTweet,$originalTweet);
            $this->sendResponseTweet($message, $plagiarizedTweet['id']);
        }

    }

    private function findOriginalTweet($plagiarizedTweet) {

        $possibleOriginalTweets = $this->searchTweets($plagiarizedTweet['text']);

        if (!empty($possibleOriginalTweets)) {

            // Instead of comparing dates, etc. we'll just retrieve the last tweet from this search
            $originalTweet = end($possibleOriginalTweets);
            if ($originalTweet['user_id']!="1495782590" && $originalTweet['favs']>self::MIN_FAVS_ORIGINAL) { // To be sure that is a plagiarism of a popular tweet
                return $originalTweet;
            }

        }

        return false;

    }

    /** Get the response message that belongs to that tweet mention **/
    private function buildMessageToReplyToPlagiarism($plagiarizedTweet,$originalTweet) {

        // Not to repeat always the same message
        $templates = array(
            "¡PLAGIO! [plagier_screen_name] te has copiado este tuit de [original_screen_name] ☞ [original_tweet_url]",
            "¡Vaya copiada [plagier_screen_name]! Gente, el tuit original es de [original_screen_name] y lo tenéis aquí: [original_tweet_url]",
            "¡Eres un plagier [plagier_screen_name]! El tuit original de esta copiada es de [original_screen_name], miradlo: [original_tweet_url]",
            "¿Otro plagio, [plagier_screen_name]? No paras, ¿eh? El tuit güeno es este de [original_screen_name] ☞ [original_tweet_url]",
            "¡Venga esas copiadas! El tuit original de este plagio lo tenéis aquí ☞ [original_tweet_url] y es de [original_screen_name]. ¡Seguidle! ✋",
            "✋ ¡STOP PLAGIOS! Tuit original de [original_screen_name] ¡más faveable y retuiteable! [original_tweet_url]. ¡Seguidle!",
            "Jo, [plagier_screen_name] tío, me pones triste, otra copiada descarada ☹ Tuit original de [original_screen_name] aquí: [original_tweet_url]"
        );

        $message = $templates[array_rand($templates)];
        $parsedMessage = $this->parseVariablesTweet($message,$plagiarizedTweet,$originalTweet);
        echo $parsedMessage . PHP_EOL;

        return $parsedMessage;

    }

    private function parseVariablesTweet($message,$plagiarizedTweet,$originalTweet) {

        $message = str_replace("[plagier_screen_name]", "@" . $plagiarizedTweet['user_screen_name'], $message);
        $message = str_replace("[original_screen_name]", "@" . $originalTweet['user_screen_name'], $message);
        $originalTweetUrl = "https://twitter.com/" . $originalTweet['user_screen_name'] . "/status/" . $originalTweet['id'];
        $message = str_replace("[original_tweet_url]", $originalTweetUrl, $message);

        return $message;


    }

}