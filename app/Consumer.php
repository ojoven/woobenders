<?php

// Require Phirehose lib to consume streaming tweets
require_once 'lib/Phirehose/Phirehose.php';
require_once 'lib/Phirehose/OauthPhirehose.php';

// Require Codebird lib to post statuses
require_once 'lib/codebird/src/codebird.php';

// Require Common Functions php
require_once 'lib/functions.php';

// And a MySQL wrapper
//require_once 'lib/MysqliDb/MysqliDb.php';

class Consumer extends OauthPhirehose {

	public $db; // Database
	public $cb; // Codebird, will handle the status updates

	protected $behaviour;

	public function __construct($behaviour) {

		// Let's assign the behaviour
		$this->behaviour = $behaviour;

		parent::__construct(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);

	}

	/** Function used by Phirehose where we set the keywords we want to track  **/
	public function checkFilterPredicates() {

		$keywords = $this->behaviour->getKeywords();
		$this->setTrack($keywords);
	}

	/** Treat Streamed Tweet **/
	public function enqueueStatus($status) {

		//This function is called automatically by the Phirehose class
		//when a new tweet is received with the JSON data in $status
		$this->behaviour->processTweet($status);

	}

}

?>