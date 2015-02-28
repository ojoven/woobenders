<?php

class DonPepito implements ResponseBehaviour {

    // Auxiliar Functions for Response Behaviour
    use ResponseFunctions;

    protected $db; // database connection
    protected $cb; // codebird (auxiliar wrapper to post tweets)

    const BOT_SCREEN_NAME = "@donpepitobot";

    /** Initialize bot: DB, codebird... **/
    public function initialize() {

        // Database
        if (USE_DB) {
            $this->db = new MysqliDb(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_DATABASE_NAME);
        }

        // Codebird
        Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
        $this->cb = Codebird::getInstance();
        $this->cb->setToken(OAUTH_TOKEN, OAUTH_SECRET);

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
            $this->responseDonPepito($tweet);

        } else {
            // We've missed some tweets, we may want to treat this exception
            // Though for this kind of bots this shouldn't happen
            $this->treatMissedTweets($tweet);
        }

    }

    /** Get the message for the mention and send tweet if applies **/
    private function responseDonPepito($tweet) {

        // Here your magic
        $message = $this->_getResponseMessageToMention($tweet);
        if ($message) {
            $this->sendResponseTweet($message, $tweet->id);
        }

    }

    /** Get the response message that belongs to that tweet mention **/
    private function _getResponseMessageToMention($tweet) {

        $text = $tweet->text;

        // Let's parse the text from the tweet
        $text = Functions::parseString($text);

        // For DonPepito Example, we'll do the following steps

        // Message 1: "Hola Don Pepito" (will reply "Hola Don José")
        // Message 2: "Pasó usted por mi casa" (will reply "Por su casa yo pasé")
        // Message 3: "Vio usted a mi abuela" (will reply "A su abuela yo le vi")
        // Message 4: "Adios Don Pepito" (will reply "Adios Don José")

        // Ok, having these messages, we'll check for keywords that should be present in each of them
        // We'll use the | as an OR, so each of the options can match, we could've used arrays, too; anyway.
        // We've made this case insensitive, too, and accent insensitive, too, so we don't have to worry about this
        $messages[0] = array('hola','don','pepito');
        $messages[1] = array('paso','ud|usted','casa');
        $messages[2] = array('vio','ud|usted','abuela');
        $messages[3] = array('adios','don','pepito');

        $replies[0] = "hola don José";
        $replies[1] = "por su casa yo pasé";
        $replies[2] = "a su abuela yo la vi";
        $replies[3] = "adios don José";

        // Let's check if the tweet matches any of the messages
        foreach ($messages as $index=>$message) {

            $matchesAllKeywords = true;
            // Let's check every keyword
            foreach ($message as $keyword) {
                if (!Functions::isKeywordInText($keyword,$text,'|')) {
                    $matchesAllKeywords = false;
                }
            }

            if ($matchesAllKeywords) {
                // We've found a message that matches, let's send it!
                return "@" . $tweet->user->screen_name . " " . $replies[$index];
            }
        }

        // We haven't found a message that matches
        return false;

    }

}