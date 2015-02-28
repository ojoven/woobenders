<?php

// Root Path
define('ROOT_PATH', __DIR__ . "/");

// Require App Models
require_once 'app/loader.php';

// Require App Settings
require_once 'app/settings.php';

class App {

	public function execute() {

		// Get Behaviour
		$behaviour = BotFactory::getBot();

		// Create new Consumer object
		$consumer = new Consumer($behaviour);

		// Start collecting tweets
		$consumer->consume();

	}

}

$app = new App();
$app->execute();