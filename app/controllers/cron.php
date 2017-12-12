<?php
/**
 * Created by PhpStorm.
 * User: rhinos
 * Date: 12/12/17
 * Time: 16:11
 */

class ControllersCron {


    /**
     * update all in one shot
     *
     * @todo too heavy : optimize this by using DB datetime
     *
     * @return bool
     */
    public static function update() {
        HelpersCron::checkLastModification();
        HelpersCron::getData();
        HelpersCron::parse();

        return true;
    }

    /**
     * debug update step
     *
     * @param int $step
     */
    public static function debug($step = 1) {
        $return = null;
        if ($step == 1) {
            $return = HelpersCron::checkLastModification();
        }

        if ($step == 2) {
            $return = HelpersCron::getData();
        }

        if ($step == 3) {
            $return = HelpersCron::parse();
        }

        return implode("<br/>", $return);
    }
}