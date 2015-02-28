<?php

// For bots that open a stream for keywords and trigger an event - send tweet, save to DB -
// when the stream is filled with a tweet for those keywords

interface ResponseBehaviour {

    public function getKeywords();
    public function processTweet($status);

}