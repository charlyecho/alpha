<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 07/01/18
 * Time: 19:23
 */

class ProHelpersFacture {
    public static function getList($user_id) {
        $db = ClassesDb::getInstance();

        $sql = "SELECT f.*, o.name as organisation_name, SUM(f2.montant) as total
            FROM facture f
            LEFT JOIN organisation o ON f.organisation_id = o.id 
            LEFT JOIN facture_line f2 ON f.id = f2.facture_id
            WHERE f.user_id = ".$db->quote($user_id)." 
            GROUP BY f.id 
            ORDER BY (CASE WHEN f.date_payment_received IS NULL THEN 1 ELSE 0 END) DESC, 
                f.date_payment_received DESC,
                (CASE WHEN f.date_send IS NULL THEN 1 ELSE 0 END) DESC,
                f.date_send DESC,
                (CASE WHEN f.date_creation IS NULL THEN 1 ELSE 0 END) DESC,
                f.date_creation DESC";
        $q = $db->prepare($sql);
        $q->execute();
        $tasks = $q->fetchAll();
        return $tasks;
    }
}