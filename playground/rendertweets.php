<?php
define('ROOT_PATH', dirname(__DIR__) . "/");
require_once ROOT_PATH . 'app/loader.php';

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
    $plagiarized->resizeInPixel(400, null, true);

    $plagiarizeLayer = ImageWorkshop::initFromPath('plagiarizelayer2.png');
    $plagiarizeLayer->resizeInPixel(300, null, true);

    $plagiarized->addLayerOnTop($plagiarizeLayer, 0, 0, 'MM');
    $plagiarized->save(__DIR__, "resultplagiarized.png", true, null, 100);

    $original = ImageWorkshop::initFromPath('original.png');
    $original->resizeInPixel(400, null, true);

    $originalLayer = ImageWorkshop::initFromPath('tickgreen.png');
    $originalLayer->resizeInPixel(50, null, true);

    $original->addLayerOnTop($originalLayer, 20, 20, 'RB');
    $original->save(__DIR__, "resultoriginal.png", true, null, 100);

    $firGroup1 = ImageWorkshop::initVirginLayer($plagiarized->getWidth(), $plagiarized->getHeight() + $original->getHeight());
    $firGroup1->addLayerOnTop($plagiarized, 0, 0, 'LT');
    $firGroup1->addLayerOnTop($original, 0, 0, 'LB');
    $firGroup1->save(__DIR__, "result.png", true, null, 100);


}

