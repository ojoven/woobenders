<?php

// Require Phirehose lib to consume streaming tweets
require_once 'lib/Phirehose/Phirehose.php';
require_once 'lib/Phirehose/OauthPhirehose.php';
// And a MySQL wrapper
require_once 'lib/MysqliDb/MysqliDb.php';

class Consumer extends OauthPhirehose {

	// A database connection is established at launch and kept open permanently
	public $db;
	public function db_connect($host,$username,$password,$db) {
		$db = new MysqliDb($host,$username,$password,$db);
		$this->db = $db;
	}

	public function init_models() {
		$this->Tweet = ClassRegistry::init('Tweet');
		$this->MissedTweet = ClassRegistry::init('MissedTweet');
	}

	/** Treat Streamed Tweet **/
	public function enqueueStatus($status) {
		//This function is called automatically by the Phirehose class
		//when a new tweet is received with the JSON data in $status
		$now = date('Y-m-d H:i:s', time());
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

	}

	private function _saveTweetDb($tweet) {

		// Let's extract / parse some interesting info from the tweet
		$tweetId = $tweet->id_str;

		// If there's a ", ', :, or ; in object elements, serialize() gets corrupted
		// You should also use base64_encode() before saving this
		$rawTweet = base64_encode(serialize($tweet));

		// Is this a retweet?
		$isRetweet = (isset($tweet->retweeted_status)) ? true : false;

		// Ok, let's save it
		$data = array(
			'tweet_id' => $tweetId,
			'raw_tweet' => $rawTweet,
			'is_retweet' => $isRetweet
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