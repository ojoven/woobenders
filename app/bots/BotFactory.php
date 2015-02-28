<?php

class BotFactory {

    public static function getBot($botName) {

        $bot = new $botName();
        return $bot;

    }

}