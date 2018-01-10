<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 07/01/18
 * Time: 14:38
 */

class ProControllersFacture {
    public static function home() {

        $user = HelpersUser::getCurrent();

        $factures = ProHelpersFacture::getList($user->id);
        $min = null;
        $list = array();
        $max = ClassesDate::getInstance()->format("Y-m-d");
        $list[$max] = 0;


        foreach ($factures as $f) {
            if ($f->date_payment_received) {
                $_date = ClassesDate::getInstance($f->date_payment_received)->format("Y-m-01");
                if (!isset($list[$_date])) {
                    $list[$_date] = 0;
                }

                if (!$min || $_date < $min) {
                    $list[$_date] += $f->total;
                    $min = $_date;
                }
            }
        }

        if ($min) {
            while ($min <= $max) {
                if (!isset($list[$min])) {
                    $list[$min] = 0;
                }
                $min = ClassesDate::getInstance($min)->modify("+1 MONTH")->format("Y-m-01");
            }
        }

        ksort($list);

        $template = ClassesTwig::getInstance();
        return $template->render("pro/views/facture.twig", array(
            "factures" => $factures,
            "stat" => array(
                "abs" => json_encode(array_keys($list)),
                "data" => json_encode(array_values($list))
            )
        ));
    }

    public static function pdf($id) {
        trace($id);
    }
}