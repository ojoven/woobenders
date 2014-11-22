<?php

// Require Phirehose lib to consume streaming tweets
require_once 'lib/Phirehose/Phirehose.php';
require_once 'lib/Phirehose/OauthPhirehose.php';
// Require Codebird lib to post statuses
require_once 'lib/codebird/src/codebird.php';
// Require Common Functions php
require_once 'lib/functions.php';
// And a MySQL wrapper
require_once 'lib/MysqliDb/MysqliDb.php';

class Consumer extends OauthPhirehose {

	public $db; // Database
	public $cb; // Codebird, will handle the status updates

	// A database connection is established at launch and kept open permanently
	public function db_connect($host,$username,$password,$db) {
		$db = new MysqliDb($host,$username,$password,$db);
		$this->db = $db;
	}

	/** Function used by Phirehose where we set the keywords we want to track  **/
	public function checkFilterPredicates() {
		$track = array();

		array_push($track, "@donpepitobot");
		$this->setTrack($track);
	}

	/** Treat Streamed Tweet **/
	public function enqueueStatus($status) {
		//This function is called automatically by the Phirehose class
		//when a new tweet is received with the JSON data in $status
		$tweet = json_decode($status);

		if (isset($tweet->id_str)) {
			// We got the tweet

			// We can render some tweet options in console
			$this->_renderTweetConsole($tweet);

			// We can save the tweet for further treatments (limits, stats, etc.)
			$this->_saveTweetDb($tweet);

			// AND HERE, YOUR APP'S MAGIC
			$this->_doMagic($tweet);

		} else {
			// We've missed some tweets, we may want to treat this exception
			// Though for this kind of bots this shouldn't happen
			$this->_treatMissedTweets($tweet);
		}
	}

	private function _doMagic($tweet) {

		// Here your magic
		$message = $this->_getResponseMessageToMention($tweet);
		if ($message) {
			$reply = $this->_sendTweet($message, $tweet->id);
		}

	}

	public function _getResponseMessageToMention($tweet) {

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
		$replies[2] = "a su abuela yo le vi";
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

	private function _sendTweet($message,$replyToTweetId) {
		\Codebird\Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
		$this->cb = \Codebird\Codebird::getInstance();
		$this->cb->setToken(OAUTH_TOKEN, OAUTH_SECRET);

		$params = array(
			'status' => $message,
			'in_reply_to_status_id' => $replyToTweetId
		);

		$reply = $this->cb->statuses_update($params);
	}

	private function _saveTweetDb($tweet) {

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

	}

	private function _renderTweetConsole($tweet) {

		// This is totally optional

		// We will print the id of the tweet consumed
		$renderTweet = $tweet->id_str;

		// And add a sufix, for example, if it's a retweet
		if (isset($tweet->retweeted_status)) {
			$renderTweet .= " --> retweet";
		}

		// Now we print to console
		echo $renderTweet.PHP_EOL;
	}

	private function _treatMissedTweets($tweet) {

		// We do nothing, this should never happen in this kind of apps
		// And if it ever happens, is not something we should worry about

	}

}

?>