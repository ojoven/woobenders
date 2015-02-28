<?php

class Consumer extends OauthPhirehose {

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
		$this->setTrack($keywords);
	}

	/** Treat Streamed Tweet **/
	public function enqueueStatus($status) {

		//This function is called automatically by the Phirehose class
		//when a new tweet is received with the JSON data in $status
		$this->bot->processTweet($status);

	}

}

?>