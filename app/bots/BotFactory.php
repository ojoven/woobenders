<?php

class BotFactory {

    public static function getBot() {

        $bot = new PlagiasTwits();
        return $bot;

    }

}