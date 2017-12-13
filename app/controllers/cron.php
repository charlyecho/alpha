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
        $report_parse = HelpersCron::parse();
        $nb_new = $report_parse->nb_new;
        return $nb_new;
    }

    /**
     * debug update step
     *
     * @param int $step
     * @return null|string
     */
    public static function debug($step = 1) {
        $sub_id = get($_GET, "id");
        $return = null;
        if ($step == 1) {
            $return = HelpersCron::checkLastModification($sub_id);
            return implode("<br/>", $return);
        }

        if ($step == 2) {
            $return = HelpersCron::getData($sub_id);
            return implode("<br/>", $return);
        }

        if ($step == 3) {
            $return = HelpersCron::parse($sub_id);
            return implode("<br/>", $return->log);
        }

        return null;
    }
}