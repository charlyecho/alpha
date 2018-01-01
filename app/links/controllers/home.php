<?php

class LinksControllersHome {
    public static function home() {
        $user = HelpersUser::getCurrent();

        $limit = 40;
        $type = get($_GET, "type");
        $nsfw = get($_GET, "nsfw", 2);
        $search = get($_GET, "search");
        $private = get($_GET, "private", 0);
        $start = get($_GET, "start", 0);
        $gallery = get($_GET, "gallery", 0);
        $_list = get($_GET, "list", "l");

        if (in_array($_list, array("g", "s"))) {
            $type = "image";
            $limit = 100;
        }

        $url = "http://".$_SERVER["HTTP_HOST"].(isset($_SERVER["DOCUMENT_URI"]) ? str_replace("/index.php", "", $_SERVER["DOCUMENT_URI"]) : null);

        $list = LinksHelpersLinks::getList($user->id, $search, $type, $nsfw, $private, $_list, $start*$limit, $limit);

        foreach($list as $l) {
            $l->_tags = array_filter(explode(" ", $l->tags));
        }

        $template = ClassesTwig::getInstance();
        return $template->render("links/views/home.twig", array(
            "links" => $list,
            "nsfw" => $nsfw,
            "private" => $private,
            "start" => $start,
            "type" => $type,
            "search" => $search,
            "list" => $_list,
            "url" => $url
        ));
    }

    public static function edit($id = null) {
        $user = HelpersUser::getCurrent();

        $feed_item_id = get($_GET, "feed_item_id");
        $url = get($_GET, "url");
        $db = ClassesDb::getInstance();

        if (!empty($_POST)) {
            LinksHelpersLinks::edit($_POST);
        }


        if ($id) {
            $sql = "SELECT * FROM link WHERE id = ".$db->quote($id)." AND user_id = ".$db->quote($user->id);
            $q = $db->prepare($sql);
            $q->execute();
            $link = $q->fetch();
        }
        else {
            $link = new stdClass();
            $link->id = null;
            $link->user_id = $user->id;
            $link->title = null;
            $link->url = null;
            $link->img = null;
            $link->content = null;
            $link->is_nsfw = 0;
            $link->is_private = 0;
            $link->type = 'link';
            $link->active = 1;
            $link->tags = null;

            if ($feed_item_id) {
                //@TODO
            }

            if ($url) {
                $link->url = urldecode($url);
                $sql = "SELECT * FROM link WHERE url = ".$db->quote($link->url)." AND user_id = ".$db->quote($user->id);
                $q = $db->prepare($sql);
                $q->execute();
                $_link = $q->fetch();
                if ($_link) {
                    $link = $_link;
                }
            }
        }


        if ($link->type == "article" && $id) {
            $link->url = "#$id";
        }


        $template = ClassesTwig::getInstance();
        return $template->render("links/views/edit.twig", array(
            "link" => $link
        ));
    }
}