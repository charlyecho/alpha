<?php
/**
 * Created by PhpStorm.
 * User: rhinos
 * Date: 14/12/17
 * Time: 13:19
 */

class RssHelpersItems {

    public static function updateStatus(array $status) {
        $db = ClassesDb::getInstance();
        foreach($status as $id => $types) {
            $sql = "UPDATE subscription_item SET read = ".$db->quote((int) $types["read"]).", starred = ".$db->quote((int) $types["starred"])." WHERE id = ".$db->quote($id);
            $db->exec($sql);
        }

    }
}