<?php

class Consumer extends OauthPhirehose {

	const TYPE_STREAMING_TRACK = "track";
	const TYPE_STREAMING_FOLLOW = "follow";

	protected $bot;

	public function __construct($bot) {

		// Let's assign the behaviour
		$this->bot = $bot;
		$this->bot->initialize();

		parent::__construct(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);

	}

	/** Function used by Phirehose where we set the keywords we want to track  **/
	public function checkFilterPredicates() {

		$keywords = $this->bot->getKeywords();

		if ($this->bot->getTypeStreaming()==self::TYPE_STREAMING_TRACK) {
			// Keywords
			$this->setTrack($keywords);
		} else {
			// User follow stream
			$this->setFollow($keywords);
		}
	}

	/** Treat Streamed Tweet **/
	public function enqueueStatus($status) {

		//This function is called automatically by the Phirehose class
		//when a new tweet is received with the JSON data in $status
		$this->bot->processTweet($status);

	}

}

?>