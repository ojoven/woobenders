<?php
define('ROOT_PATH', dirname(__DIR__) . "/");
require_once ROOT_PATH . 'app/loader.php';
require_once ROOT_PATH . 'lib/simple_html_dom.php';

wiktionary();
//eduscol();
//languageDaily();

function wiktionary() {
    $url = "http://fr.wiktionary.org/wiki/Wiktionnaire:Liste_de_1750_mots_fran%C3%A7ais_les_plus_courants#.C3.80_la_ferme";
    $html_origin = file_get_contents("origin_wiktionary.html");
    //$html_origin = file_get_contents($url);
    //file_put_contents('origin_wiktionary.html',$html_origin);
    $html = str_get_html($html_origin);

    foreach ($html->find('h2') as $header) {
        if ($header->find('span',0) && trim($header->find('span',0)->plaintext)!="Introduction") {
            echo $header->find('span',0)->plaintext . PHP_EOL;
        }
    }

    foreach ($html->find('h3') as $subheader) {
        $fatherTag = "";
        while ($fatherTag!="h2") {
            $fatherTag = $subheader->
        }
    }
}

/** EDUSCOL 1500 mots plus utilisés **/
function eduscol() {
    $url = "http://eduscol.education.fr/cid47916/liste-des-mots-classee-par-frequence-decroissante.html";
    $html = file_get_html($url);

    $table = $html->find('.divTable table',0);
    $words = array();
    $numRow = 0;
    foreach ($table->find('tr') as $row) {
        $numRow++;
        if ($numRow==1) continue;
        $word['frequency'] = $row->find('td',0)->plaintext;
        $word['word'] = $row->find('td',1)->plaintext;
        $word['type'] = $row->find('td',2)->plaintext;

        array_push($words,$word);
    }

    $json = json_encode($words);
    file_put_contents('result_eduscol.json',$json);
    echo $json . PHP_EOL;
}

/** LANGUAGE DAILY **/
function languageDaily() {

    $arrayUrls = array(
        'http://french.languagedaily.com/wordsandphrases/most-common-words',
        'http://french.languagedaily.com/wordsandphrases/common-french-words',
        'http://french.languagedaily.com/wordsandphrases/most-common-words-3',
        'http://french.languagedaily.com/wordsandphrases/most-common-french-words',
        'http://french.languagedaily.com/wordsandphrases/most-common-words-5',
        'http://french.languagedaily.com/wordsandphrases/most-common-words-6',
        'http://french.languagedaily.com/wordsandphrases/most-common-words-7',
        'http://french.languagedaily.com/wordsandphrases/most-common-words-8',
        'http://french.languagedaily.com/wordsandphrases/most-common-words-9',
        'http://french.languagedaily.com/wordsandphrases/most-common-words-10',
        'http://french.languagedaily.com/wordsandphrases/most-common-words-11',
        'http://french.languagedaily.com/wordsandphrases/most-common-words-12'
    );

    $words = array();

    foreach ($arrayUrls as $indexUrl=>$url) {
        $html = file_get_html($url);

        $indexWord = ($indexUrl==0) ? 2 : 1;
        $indexTranslation = ($indexUrl==0) ? 3 : 2;
        $indexType = ($indexUrl==0) ? 4 : 3;

        $table = $html->find('.vocabulary',0);
        $numRow = 0;
        foreach ($table->find('tr') as $row) {
            $numRow++;
            if ($numRow==1) continue;
            $word['ranking'] = $row->find('td',0)->plaintext;
            $word['word'] = $row->find('td',$indexWord)->plaintext;
            $word['translation'] = $row->find('td',$indexTranslation)->plaintext;
            $word['type'] = $row->find('td',$indexType)->plaintext;

            array_push($words,$word);
        }
    }

    $json = json_encode($words);
    file_put_contents('result_language_daily.json',$json);
    echo $json . PHP_EOL;
}


/** FLASHCARDS **/
$url = "http://quizlet.com/10716652/the-2000-most-common-french-words-flash-cards/";