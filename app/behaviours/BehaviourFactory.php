<?php

class BehaviourFactory {

    public static function getBehaviour() {

        $behaviour = new ResponseBehaviour();
        return $behaviour;

    }

}