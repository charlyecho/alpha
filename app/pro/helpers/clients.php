<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 07/01/18
 * Time: 19:23
 */

class ProHelpersClients {
    public static function getList($user_id) {
        $db = ClassesDb::getInstance();
        $sql = "SELECT * FROM organisation WHERE user_id = ".$db->quote($user_id);
        $q = $db->prepare($sql);
        $q->execute();

        return $q->fetchAll();

    }
}