<?php

class BotFactory {

    public static function getBot() {

        $bot = new DonPepito();
        return $bot;

    }

}