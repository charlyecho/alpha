<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 25/12/17
 * Time: 16:06
 */

class LinksHelpersLinks {
    public static function getList($user_id) {
        $db = ClassesDb::getInstance();
        $s = $db->prepare("SELECT * FROM link WHERE user_id = ".$db->quote($user_id)." ORDER BY creation_date DESC");
        $s->execute();
        return $s->fetchAll();
    }
}