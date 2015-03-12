<?php
define('ROOT_PATH', dirname(__DIR__) . "/");
require_once ROOT_PATH . 'app/loader.php';

$bot = BotFactory::getBot("PlagiasTwits");
$bot->initialize();

// Codebird
Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
$cb = Codebird::getInstance();
$cb->setToken(OAUTH_TOKEN, OAUTH_SECRET);

// plagiarized
$id = "575784844124295169";
$params = array('id'=>$id);
$plagiarized = $cb->statuses_show_ID($params);
print_r($plagiarized);

/**
// original
$id = "574192636992577538";
$params = array('id'=>$id);
$original =  $bot->parseTweet($cb->statuses_show_ID($params));**/

$bot->plagiasTwitsMagic($plagiarized);