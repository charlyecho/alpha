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
    public static function update($step = null) {
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
        $sub_id = get($_GET, "id");
        $return = null;
        if ($step == 1) {
            $return = HelpersCron::checkLastModification($sub_id);
        }

        if ($step == 2) {
            $return = HelpersCron::getData($sub_id);
        }

        if ($step == 3) {
            $return = HelpersCron::parse($sub_id);
        }

        return implode("<br/>", $return);
    }
}