<?php

// Require App Settings
require_once 'app/settings.php';

// Require App Models
require_once 'app/consumer.php';

class App {

	public function execute() {

		// Create new Consumer object
		$consumer = new Consumer(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);

		// Establish a MySQL database connection
		$consumer->db_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME);

		// Init Models
		$consumer->init_models();

		// Start collecting tweets
		// Automatically calls enqueueStatus($status) with each tweet's JSON data
		$consumer->consume();

	}

}

$app = new App();
$index->execute;
