<?php
class HelpersCategory {
    public static function getItem($id) {
        $user = HelpersUser::getCurrent();
        if ($id == "new") {
            $cat = new stdClass();
            $cat->id = null;
            $cat->name = "";
            $cat->color = "#ffffff";
            $cat->user_id = $user->id;
            $cat->nb_subs = 0;
            return $cat;
        }
        else {
            $db = ClassesDb::getInstance();
            $sql = "SELECT c.*, count(s.id) as nb_subs FROM category c LEFT JOIN subscription s ON c.id = s.category_id WHERE c.user_id = ".$db->quote($user->id)." AND c.id = ".$db->quote($id);
            $q = $db->prepare($sql);
            $q->execute();
            return $q->fetch();
        }
    }

    public static function editItem($post) {
        $name = get($post, "name");
        $id = get($post, "id");
        $user_id = get($post, "user_id");
        $color = get($post, "color", "ffffff");
        $del = get($post, "del", 0);

        $db = ClassesDb::getInstance();
        if ($id) {
            if ($del) {
                $sql = "DELETE FROM category WHERE id =" . $db->quote($id) . ";";
            }
            else {
                $sql = "UPDATE category SET color= ".$db->quote($color).", name = " . $db->quote($name) . ", user_id = " . $db->quote($user_id) . " WHERE  id =" . $db->quote($id) . ";";
            }
        }
        else {
            $sql = "INSERT INTO category (user_id, name, color) VALUES ( ".$db->quote($user_id).", ".$db->quote($name).", ".$db->quote($color)." );";
        }
        try {
            $q = $db->prepare($sql);
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