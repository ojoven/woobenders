<?php

class FilmDisaffinity implements ResponseBehaviour {

    // Auxiliar Functions for Response Behaviour
    use ResponseFunctions;

    protected $db; // database connection
    protected $cb; // codebird (auxiliar wrapper to post tweets)
    protected $type_streaming; // stream keywords or user

    const BOT_SCREEN_NAME = "@filmdisaffinity";

    /** Initialize bot: DB, codebird... **/
    public function initialize() {

        // First, let's initialize the settings
        $this->initSettings();

        // Keywords track or user follow streaming
        $this->type_streaming = Consumer::TYPE_STREAMING_TRACK;

        // Database
        if (USE_DB) {
            $this->db = new MysqliDb(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME);
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
        array_push($keywords, self::BOT_SCREEN_NAME);

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
            $this->responseFilmDisaffinity($tweet);

        } else {
            // We've missed some tweets, we may want to treat this exception
            // Though for this kind of bots this shouldn't happen
            $this->treatMissedTweets($tweet);
        }

    }

    /** Get the message for the mention and send tweet if applies **/
    private function responseFilmDisaffinity($tweet) {

        /**
        // Here your magic
        $message = $this->_getResponseMessageToMention($tweet);
        if ($message) {
            $this->sendResponseTweet($message, $tweet->id);
        }
        **/
    }

    /** Get the response message that belongs to that tweet mention **/
    private function _getResponseMessageToMention($tweet) {

        $text = $tweet->text;

        // Let's parse the text from the tweet
        $text = Functions::parseString($text);

        //return "@" . $tweet->user->screen_name . " " . $replies[$index];

    }

}