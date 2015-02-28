<?php

define('ROOT_PATH', __DIR__ . "/");
define('BASE_URL', "http://localhost/woobenders/");
require_once 'app/loader.php';

$botName = $_GET['bot'];

$bot = BotFactory::getBot($botName);
$settings = $bot->initSettings();

$bot->getTokens();


