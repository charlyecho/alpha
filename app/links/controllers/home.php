<?php

class LinksControllersHome {
    public static function home() {
        $user = HelpersUser::getCurrent();

        $type = get($_GET, "type");
        $nsfw = get($_GET, "nsfw", 2);
        $search = get($_GET, "search");
        $private = get($_GET, "private", 2);
        $start = get($_GET, "start", 0);
        $gallery = get($_GET, "gallery", 0);

        $url = "http://".$_SERVER["HTTP_HOST"].(isset($_SERVER["DOCUMENT_URI"]) ? str_replace("/index.php", "", $_SERVER["DOCUMENT_URI"]) : null);

        $list = LinksHelpersLinks::getList($user->id, $search, $type, $nsfw, $private, $gallery, $start*40);

        $template = ClassesTwig::getInstance();
        return $template->render("links/views/home.twig", array(
            "links" => $list,
            "nsfw" => $nsfw,
            "private" => $private,
            "start" => $start,
            "type" => $type,
            "search" => $search,
            "gallery" => $gallery,
            "url" => $url
        ));
    }

    public static function edit($id = null) {
        $user = HelpersUser::getCurrent();

        $feed_item_id = get($_GET, "feed_item_id");
        $url = get($_GET, "url");

        if (!empty($_POST)) {
            LinksHelpersLinks::edit($_POST);
        }


        if ($id) {
            $db = ClassesDb::getInstance();
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
            }
        }


        $template = ClassesTwig::getInstance();
        return $template->render("links/views/edit.twig", array(
            "link" => $link
        ));
    }
}