<?php

// Optional methods for ResponseBehaviour that could be useful, instead.

trait ResponseFunctions {

    // Settings
    public function initSettings() {
        require_once "Settings" . get_class() . ".php";
    }

    // Oauth
    public function getTokens() {

        session_start();

        Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
        $cb = Codebird::getInstance();

        if (! isset($_SESSION['oauth_token']) || isset($_GET['clear'])) {
            // get the request token
            $reply = $cb->oauth_requestToken(array(
                'oauth_callback' => BASE_URL . 'oauth.php'
            ));

            print_r($reply);

            // store the token
            $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
            $_SESSION['oauth_token'] = $reply->oauth_token;
            $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
            $_SESSION['oauth_verify'] = true;

            // redirect to auth website
            $auth_url = $cb->oauth_authorize();
            header('Location: ' . $auth_url);
            die();

        } elseif (isset($_GET['oauth_verifier']) && isset($_SESSION['oauth_verify'])) {
            // verify the token
            $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
            unset($_SESSION['oauth_verify']);

            // get the access token
            $reply = $cb->oauth_accessToken(array(
                'oauth_verifier' => $_GET['oauth_verifier']
            ));

            // store the token (which is different from the request token!)
            $_SESSION['oauth_token'] = $reply->oauth_token;
            $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;

            // send to same URL, without oauth GET parameters
            header('Location: ' . BASE_URL . 'oauth.php');
            die();
        }

        if (isset($_SESSION['oauth_token'])) {
            echo "OAUTH_TOKEN: " . $_SESSION['oauth_token'] . "<br>";
            echo "OAUTH_TOKEN_SECRET: " . $_SESSION['oauth_token_secret'] . "<br>";
        } else {
            echo "Connecting...";
        }
    }

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

    public function parseTweet($status) {

        $status = json_decode(json_encode($status),true);

        $tweet['id'] = $status['id'];
        $tweet['text'] = Functions::cleanUrls($status['text']);
        $tweet['user_id'] = $status['user']['id'];
        $tweet['user_screen_name'] = $status['user']['screen_name'];
        $tweet['created'] = strtotime($status['created_at']);

        $tweet['is_retweet'] = (isset($status['retweeted_status'])) ? true : false;
        $tweet['retweets'] = $status['retweet_count'];
        $tweet['favs'] = $status['favorite_count'];

        return $tweet;
    }

    public function searchTweets($text) {
        $params = array('q'=>$text . "-filter:retweets", 'count' => 100);
        $response = (array) $this->cb->search_tweets($params);

        $parsedTweets = array();

        if ($response['httpstatus']=="200") {
            $data = $response['statuses'];

            foreach ($data as $tweet) {
                $parsedTweet = $this->parseTweet($tweet);
                array_push($parsedTweets,$parsedTweet);
            }
        }

        return $parsedTweets;
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