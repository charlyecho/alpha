<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 03/12/17
 * Time: 15:25
 */

class HelpersSubscription {

    public static function getItem($id = null) {
        $user = HelpersUser::getCurrent();
        if ($id == "new") {
            $sub = new stdClass();
            $sub->id = null;
            $sub->name = null;
            $sub->url = null;
            $sub->user_id = $user->id;
            $sub->category_id = null;
            return $sub;
        }
        else {
            $db = ClassesDb::getInstance();
            $sql = "SELECT s.* FROM subscription s WHERE s.user_id = ".$db->quote($user->id)." AND s.id = ".$db->quote($id);
            $q = $db->prepare($sql);
            $q->execute();
            return $q->fetch();
        }
    }

    public static function editItem($post) {
        $id = get($post, "id");
        $del = get($post, "del");
        $user_id = get($post, "user_id");
        $category_id = get($post, "category_id");
        $url_site = get($post, "url_site");
        $name = get($post, "name");
        $url = get($post, "url");

        if ($url_site && $id) {
            $_url = "https://www.google.com/s2/favicons?domain=".urlencode($url_site);
            $data = file_get_contents($_url);
            file_put_contents(__DIR__."/../cache/icons/".$id.".png", $data);
        }


        $db = ClassesDb::getInstance();
        if ($id) {
            if ($del) {
                $sql = "DELETE FROM subscription WHERE id = ".$db->quote($id)." AND user_id = ".$db->quote($user_id);
            }
            else {
                $sql = "UPDATE subscription SET url_site = ".$db->quote($url_site).", category_id=".$db->quote($category_id).", name = ".$db->quote($name)." WHERE id = ".$db->quote($id)." AND user_id = ".$db->quote($user_id);
            }
        }
        else {
            $sql = "INSERT INTO subscription (name, user_id, category_id, url, url_site) VALUES (".$db->quote($name ? $name : $url).", ".$db->quote($user_id).", ".$db->quote($category_id).", ".$db->quote($url).", ".$db->quote($url_site).")";
        }
        $q = $db->prepare($sql);
        try {
            if (!$q->execute()) {
                return $q->errorInfo();
            }

            if (!$id && !$del) {
                $_id = $db->lastInsertId();
                ControllersCron::update($_id);
            }
            return null;
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function moveItem($sub_id, $cat_id = null) {

        $db = ClassesDb::getInstance();
        $sql = "UPDATE subscription SET category_id = ".($cat_id ? $db->quote($cat_id) : "NULL")." WHERE id =" . $db->quote($sub_id) . ";";
        $q = $db->prepare($sql);
        try {
            if (!$q->execute()) {
                return $q->errorInfo();
            }
            return null;
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }
}