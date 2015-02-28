<?php

define('ROOT_PATH', __DIR__ . "/");
define('BASE_URL', "http://localhost/woobenders/");
require_once 'app/loader.php';

$bot = BotFactory::getBot();
$settings = $bot->initSettings();

$bot->getTokens();


