<?php
define('ROOT_PATH', dirname(__DIR__) . "/");
require_once ROOT_PATH . 'app/loader.php';

$bot = BotFactory::getBot("PlagiasTwits");
$bot->initSettings();

// Codebird
Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
$cb = Codebird::getInstance();
$cb->setToken(OAUTH_TOKEN, OAUTH_SECRET);

$searchQuery = "Odio cuando le haces a alguien un cumplido sincero sobre su bigote y luego ella ya no quiere ser tu amiga nunca más";

$params = array('q'=>$searchQuery . "-filter:retweets", 'count' =>100);
$response = (array) $cb->search_tweets($params);
print_r($response);

if ($response['httpstatus']=="200") {
    $data = $response['statuses'];
    file_put_contents("json",json_encode($data));
    echo count($data) . " resultados" . PHP_EOL;
}

if (is_array($data)) {
    $last = array_pop($data);
    print_r($last);
}