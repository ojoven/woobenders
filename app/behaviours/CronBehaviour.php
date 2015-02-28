<?php

// For bots that send a tweet with an specific frequency - counters, etc.

interface CronBehaviour {

    public function isTimeToSend();
    public function sendTweet();

}