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
     * @return int
     */
    public static function update($id = null) {
        $report_1 = HelpersCron::checkLastModification($id);
        $report_2 = HelpersCron::getData($id);
        $report_parse = HelpersCron::parse2($id);
        return $report_parse."\n";
    }

    public static function cliUpdate() {
        $report_check = HelpersCron::checkLastModification();
        foreach($report_check as $_line) {
            echo $_line."\n";
        }
        echo "-------\n";
        $report_get_data = HelpersCron::getData();
        foreach($report_get_data as $_line) {
            echo $_line."\n";
        }
        echo "-------\n";
        $report_parse = HelpersCron::parse2();
        echo $report_parse."\n";

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
            $return = HelpersCron::parse2($sub_id);
            return implode("<br/>", $return);
        }

        return null;
    }
}