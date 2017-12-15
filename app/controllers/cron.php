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
        HelpersCron::checkLastModification($id);
        HelpersCron::getData($id);
        $report_parse = HelpersCron::parse($id);
        $nb_new = $report_parse->nb_new;
        return $nb_new."\n";
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
        foreach($report_parse as $_line) {
            echo $_line."\n";
        }
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