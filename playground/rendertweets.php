<?php
define('ROOT_PATH', dirname(__DIR__) . "/");
require_once ROOT_PATH . 'app/loader.php';

$bot = BotFactory::getBot("PlagiasTwits");
$bot->initialize();

$bot->treatScreenshots();

// Codebird
Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
$cb = Codebird::getInstance();
$cb->setToken(OAUTH_TOKEN, OAUTH_SECRET);

// plagiarized
$id = "574685217866170368";
$params = array('id'=>$id);
$plagiarized = $bot->parseTweet($cb->statuses_show_ID($params));
print_r($plagiarized);

// original
$id = "574192636992577538";
$params = array('id'=>$id);
$original =  $bot->parseTweet($cb->statuses_show_ID($params));

$mediaId = $bot->getScreenshotTweets($plagiarized,$original);
echo "Media: " . $mediaId . PHP_EOL;
//$bot->sendResponseTweet("Estamos haciendo unas pruebas ;)", $plagiarized['id'], $mediaId);
return;

require_once ROOT_PATH . 'playground/ImageWorkshop/vendor/autoload.php';
use PHPImageWorkshop\ImageWorkshop;

/**
// Generate screenshot plagiarized
$urlPlagiarized = "http://twitter.com/escupotwits/status/574685217866170368";
exec("phantomjs --ssl-protocol=any rendertweets.js " . $urlPlagiarized . " plagiarized");

// Generate screenshot original
$urlOriginal = "https://twitter.com/Francisk1t0/status/574192636992577538";
exec("phantomjs --ssl-protocol=any rendertweets.js " . $urlOriginal . " original");
 **/

if (file_exists("plagiarized.png") && file_exists("original.png")) {

    // Plagiarized
    $plagiarized = ImageWorkshop::initFromPath('plagiarized.png');
    $plagiarized->resizeInPixel(600, null, true);

    $plagiarizeLayer = ImageWorkshop::initFromPath('plagiarizelayer.png');
    $plagiarizeLayer->resizeInPixel(500, null, true);

    $plagiarized->addLayerOnTop($plagiarizeLayer, 0, 0, 'MM');
    $plagiarized->save(__DIR__, "resultplagiarized.png", true, null, 100);

    $original = ImageWorkshop::initFromPath('original.png');
    $original->resizeInPixel(600, null, true);

    $originalLayer = ImageWorkshop::initFromPath('tickgreen.png');
    $originalLayer->resizeInPixel(80, null, true);

    $original->addLayerOnTop($originalLayer, 40, 40, 'RB');
    $original->save(__DIR__, "resultoriginal.png", true, null, 100);

    $firGroup1 = ImageWorkshop::initVirginLayer($plagiarized->getWidth(), $plagiarized->getHeight() + $original->getHeight());
    $firGroup1->addLayerOnTop($plagiarized, 0, 0, 'LT');
    $firGroup1->addLayerOnTop($original, 0, 0, 'LB');
    $firGroup1->save(__DIR__, "result.png", true, null, 100);

}

