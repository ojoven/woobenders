<?php

// Optional methods for ResponseBehaviour that could be useful, instead.

trait ResponseFunctions {

    // Save on DB
    public function saveTweetDb($tweet) {

        // Let's see if the configuration is correct to save the tweet
        if (USE_DB && !$this->db) {
            throw new Exception("The database must be previously configured");
        } elseif(!USE_DB) {
            return false;
        }

        // Let's extract / parse some interesting info from the tweet
        $tweetId = $tweet->id_str;
        $text = $tweet->text;
        $isRetweet = (isset($tweet->retweeted_status)) ? true : false;
        $username = $tweet->user->screen_name;

        // If there's a ", ', :, or ; in object elements, serialize() gets corrupted
        // You should also use base64_encode() before saving this
        $rawTweet = base64_encode(serialize($tweet));

        // Now
        $now = date('Y-m-d H:i:s', time());

        // Ok, let's save it
        $data = array(
            'tweet_id' => $tweetId,
            'text' => $text,
            'username' => $username,
            'raw_tweet' => $rawTweet,
            'retweet' => $isRetweet,
            'created_date' => $now
        );

        $id = $this->db->insert('tweets', $data);
        return $id;

    }

    public function sendResponseTweet($message,$replyToTweetId) {

        $params = array(
            'status' => $message,
            'in_reply_to_status_id' => $replyToTweetId
        );

        $reply = $this->cb->statuses_update($params);
    }

    // Render on console
    public function renderTweetConsole($tweet) {

        // We will print the id of the tweet consumed
        $renderTweet = $tweet->id_str;

        // And add a sufix, for example, if it's a retweet
        if (isset($tweet->retweeted_status)) {
            $renderTweet .= " --> retweet";
        }

        // Now we print to console
        echo $renderTweet.PHP_EOL;
    }

    // Treat missed tweets
    public function treatMissedTweets($status) {

        print_r($status);

    }

}