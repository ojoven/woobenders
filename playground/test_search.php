<?php
define('ROOT_PATH', dirname(__DIR__) . "/");
require_once ROOT_PATH . 'app/loader.php';

$bot = BotFactory::getBot("PlagiasTwits");
$bot->initSettings();

// Codebird
Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
$cb = Codebird::getInstance();
$cb->setToken(OAUTH_TOKEN, OAUTH_SECRET);

$searchQuery = "A este país más que levantarlo lo que hace falta es darle la vuelta, como a los colchones.";

$params = array('q'=>$searchQuery . "-filter:retweets", 'count' =>100);
$response = (array) $cb->search_tweets($params);

if ($response['httpstatus']=="200") {
    $data = $response['statuses'];
    file_put_contents("json",json_encode($data));
    echo count($data) . " resultados" . PHP_EOL;
}

