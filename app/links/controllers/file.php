<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 25/12/17
 * Time: 20:53
 */

class LinksControllersFile {
    public static function import() {
        $user = HelpersUser::getCurrent();
        $db = ClassesDb::getInstance();
        $sql = "SELECT url FROM link WHERE user_id = ".$db->quote($user->id);
        $q = $db->prepare($sql);
        $q->execute();
        $list = $q->fetchAll(PDO::FETCH_COLUMN);

        $path = __DIR__."/export.json";
        $json = file_get_contents($path);
        $array = json_decode($json);

        $nb = 0;
        foreach($array as $a) {
            if (!in_array($a->url, $list)) {

                $sql  = "INSERT INTO link (user_id, 
                  url, 
                  title, 
                  img, 
                  content, 
                  is_nsfw, 
                  is_private, 
                  creation_date, 
                  type, 
                  active,
                  tags) ";

                $sql .= "VALUES(";
                $sql .= $db->quote($user->id);
                $sql .= ", ".$db->quote($a->url);
                $sql .= ", ".$db->quote($a->title);
                $sql .= ", ".$db->quote($a->img);
                $sql .= ", ".$db->quote($a->content);
                $sql .= ", ".$db->quote($a->is_nsfw);
                $sql .= ", ".$db->quote($a->is_private);
                $sql .= ", ".$db->quote($a->creation_date);
                $sql .= ", ".$db->quote($a->type ? $a->type : "link");
                $sql .= ", ".$db->quote(1);
                $sql .= ", ".$db->quote($a->tags);
                $sql .= ")";
                $db->exec($sql);
                $nb ++;
            }

            if ($nb >= 500) {
                break;
            }
        }

        if (!$nb) {
            $sql = "UPDATE link SET type = 'link' WHERE type = ''";
            $db->exec($sql);
        }

        trace($nb);
    }
}