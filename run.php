<?php

// Root Path
define('ROOT_PATH', __DIR__ . "/");

// Require App Models
require_once 'app/loader.php';

class App {

	public function execute($botName) {

		// Get Behaviour
		$behaviour = BotFactory::getBot($botName);

		// Create new Consumer object
		$consumer = new Consumer($behaviour);

		// Start collecting tweets
		$consumer->consume();

	}

}

try {

	// We have to pass the bot name
	if (!isset($argv[1])) {
		throw new Exception("You must include the bot name");
	}

	$botName = $argv[1];
	if (!file_exists(ROOT_PATH . "app/bots/" . $botName . ".php")) {
		throw new Exception("There's no bot created with name: " . $botName . PHP_EOL . "Please create it on app/bots/" . $botName . ".php");
	}

	$app = new App();
	$app->execute($botName);

} catch (Exception $e) {

	echo $e->getMessage() . PHP_EOL;

}