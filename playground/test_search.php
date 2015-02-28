<?php
define('ROOT_PATH', dirname(__DIR__) . "/");
require_once ROOT_PATH . 'app/loader.php';
require_once ROOT_PATH . 'app/settings.php';

// Codebird
Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
$cb = Codebird::getInstance();
$cb->setToken(OAUTH_TOKEN, OAUTH_SECRET);

$searchQuery = "20.000 € piden por esto en ARCO. Decidme que no hubieseis pagado más por ese vaso en un festival de esos que vais. http://t.co/0eLPEkwSyh";

echo cleaner($searchQuery) . PHP_EOL;

return;
$params = array('q'=>$searchQuery . "-filter:retweets");
$reply = (array) $cb->search_tweets($params);
print_r($reply);



if ($reply['httpstatus']=="200") {
    $data = $reply['statuses'];
    parseTweets($data);
}

function cleaner($url) {
    return preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $url);
}

function parseTweets($statuses) {

    $tweets = array();

    foreach ($statuses as $status) {

        $tweet['id'] = $status['id'];
        $tweet['text'] = cleaner($status['text']);
        $tweet['user_id'] = $status['user']['id'];
        $tweet['user_screen_name'] = $status['user']['screen_name'];

        $tweet['retweets'] = $status['retweet_count'];
        $tweet['favs'] = $status['fav_count'];

        $tweet['created'] = strtotime($status['created_at']);

        $tweet['media_url'] = (isset($status['entities']['media'])) ? $status['entities']['media_url'] : false;

        return $tweet;

    }

}
