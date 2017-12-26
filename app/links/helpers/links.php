<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 25/12/17
 * Time: 16:06
 */

class LinksHelpersLinks {
    public static function getList($user_id, $search = null, $type = null, $nsfw = null, $private = null, $start = 0) {
        $db = ClassesDb::getInstance();
        $sql = "SELECT * FROM link WHERE user_id = ".$db->quote($user_id);
        if ($search) {
            $like = $db->quote("%".$search."%");
            $sql .= " AND (title LIKE ".$like." OR url LIKE ".$like." OR tag LIKE ".$like.")";
        }
        if ($type) {
            $sql .= " AND type = ".$db->quote($type);
        }
        if ($nsfw == 1) {
            $sql .= " AND is_nsfw = 1";
        }
        if ($nsfw == 2) {
            $sql .= " AND is_nsfw = 0";
        }
        if ($private == 1) {
            $sql .= " AND is_private = 1";
        }
        if ($private == 2) {
            $sql .= " AND is_private = 0";
        }
        $sql .= " ORDER BY creation_date DESC LIMIT ".(int) $start.", 40";
        $s = $db->prepare($sql);
        $s->execute();
        return $s->fetchAll();
    }

    public static function edit($post) {
        $db = ClassesDb::getInstance();

        $id = get($post, "id");
        $user_id = get($post, "user_id");
        $title = get($post, "title");
        $url = get($post, "url");
        $img = get($post, "img");
        $content = get($post, "content");
        $is_nsfw = get($post, "is_nsfw");
        $is_private = get($post, "is_private");
        $type = get($post, "type");
        $tag = get($post, "tag");
        $del = get($post, "del");
        $dialog = get($post, "dialog");

        if ($id) {
            if ($del) {
                $sql = "DELETE FROM link WHERE id = " . $db->quote($id);
                $db->exec($sql);
            } else {
                //update
                $sql = "UPDATE link SET 
                    user_id = ".$db->quote($user_id).",
                    title = ".$db->quote($title).",
                    url = ".$db->quote($url).",
                    img = ".$db->quote($img).",
                    content = ".$db->quote($content).",  
                    is_nsfw = ".$db->quote($is_nsfw).",  
                    is_private = ".$db->quote($is_private).",  
                    type = ".$db->quote($type).",  
                    tag = ".$db->quote($tag)."  
                    WHERE id =".$db->quote($id);
                $db->exec($sql);
            }
        }
        else {
            // insert
            $sql = "INSERT INTO link (user_id, url, title, img, content, is_nsfw, is_private, type, creation_date, tag) VALUES( 
                    ".$db->quote($user_id).",
                    ".$db->quote($url).",
                    ".$db->quote($title).",
                    ".$db->quote($img).",
                    ".$db->quote($content).",  
                    ".$db->quote($is_nsfw).",  
                    ".$db->quote($is_private).",  
                    ".$db->quote($type).",  
                    ".$db->quote(ClassesDate::getInstance()->toSql()).",
                    ".$db->quote($tag).");";
            $db->exec($sql);
        }


        if ($dialog) {
            echo "<script>window.close();</script>";
            exit();
        }

        if ($id) {
            if ($del) {
                ClassesSession::addMessage("Link deleted");
            }
            else {
                ClassesSession::addMessage("Link succefully edited");
            }
            redirect("/links");
        }

    }
}