<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 01/12/17
 * Time: 21:32
 */

class RssControllersFeeds {
    public static function home() {
        $user = HelpersUser::getCurrent();

        $tree = RssHelpersFeeds::getFeedTree($user->id);
        $nb = 0;
        foreach($tree as $t) {
            $nb += count($t->subs);
        }
        if (!$nb) {
            ClassesSession::addMessage("You don't have any subscription yet");
            redirect("/rss/config");
        }

        $template = ClassesTwig::getInstance();
        return $template->render("rss/views/home.twig", array(
            "tree" => $tree,
            "cron" => is_file(__DIR__."/../cache/cron.txt")
        ));
    }

    public static function ajax() {
        $user = HelpersUser::getCurrent();
        $date = ClassesDate::getInstance()->toSql();

        $cat_id = get($_POST, "cat_id");
        $sub_id = get($_POST, "sub_id");
        $read = get($_POST, "read", 0);
        $starred = get($_POST, "starred", 0);
        $nsfw = get($_POST, "nsfw", 0);
        $status = get($_POST, "status", array());   // status to update (read, starred)

        RssHelpersItems::updateStatus($status);

        // subscriptions count unread
        $nb = json_encode(RssHelpersFeeds::getNbSub($user->id), JSON_OBJECT_AS_ARRAY);

        // items
        $items = RssHelpersFeeds::getSubItems($user->id, $cat_id, $sub_id, $read, $starred, $nsfw);
        $_items_json = array();
        foreach($items as $i) {
            $_item = new stdClass();
            $_item->id = $i->id;
            $_item->read = $i->read;
            $_item->starred = $i->starred;
            $_items_json[$i->id] = $_item;
        }
        $items_json = json_encode($_items_json);

        $template = ClassesTwig::getInstance();
        return $template->render("rss/views/ajax.twig", array(
            "count" => $nb,
            "items" => $items,
            "nsfw" => $nsfw,
            "starred" => $starred,
            "read" => $read,
            "items_json" => $items_json
        ));
    }

    /**
     * feed managment view
     *
     * @return string
     */
    public static function config() {
        $user = HelpersUser::getCurrent();

        $_categories = RssHelpersFeeds::getCategoryList($user->id);
        $subs = RssHelpersFeeds::getSubList($user->id);

        // remplissage cats
        $categories = array();

        $nstd = new stdClass();
        $nstd->id = null;
        $nstd->name = "uncategorized";
        $nstd->subs = array();
        $categories["nocat"] = $nstd;
        foreach($_categories as $c) {
            if (!isset($c->subs)) {
                $c->subs = array();
            }
            $categories[$c->id] = $c;
        }
        foreach($subs as $_sub) {
            if ($_sub->category_id) {
                $categories[$_sub->category_id]->subs[$_sub->id] = $_sub;
            }
            else {
                $categories["nocat"]->subs[$_sub->id] = $_sub;
            }
        }

        $template = ClassesTwig::getInstance();
        return $template->render("rss/views/config.twig", array(
            "categories" => $categories
        ));
    }

    /**
     * export user's feeds in ompl xml file
     *
     * @return string
     */
    public static function export() {
        $user = HelpersUser::getCurrent();
        $tree = RssHelpersFeeds::getFeedTree($user->id);
        $date = ClassesDate::getInstance()->format("Y-m-d_H-i");

        header( 'Content-Type: text/xml; charset=UTF-8', true );
        header('Content-Disposition: attachment; filename="'.$date.'_export.opml"');

        $template = ClassesTwig::getInstance();
        return $template->render("rss/views/export.twig", array(
            "tree" => $tree
        ));
    }

    /**
     * import ompl feed file
     */
    public static function import() {
        $user = HelpersUser::getCurrent();



        $file = isset($_FILES["file"]) ? $_FILES["file"] : null;
        if (!$file) {
            return;
        }

        $_file = $file["tmp_name"];
        $xml = simplexml_load_file($_file);
        $json = json_encode($xml, JSON_PRETTY_PRINT);
        $std = json_decode($json);

        RssHelpersFeeds::import($user->id, $std);

    }

    public static function ajax_edit_category($id = null) {
        $user = HelpersUser::getCurrent();

        $category = RssHelpersCategory::getItem($id);
        $categories = RssHelpersFeeds::getCategoryList($user->id);

        $template = ClassesTwig::getInstance();
        return $template->render("rss/views/ajax_category.twig", array(
            "category" => $category,
            "categories"=> $categories
        ));
    }

    public static function ajax_post_category() {
        $msg = RssHelpersCategory::editItem($_POST);
        redirect("/rss/config");
    }

    public static function ajax_post_subscription() {
        $msg = RssHelpersSubscription::editItem($_POST);
        redirect("/rss/config");
    }

    public static function move_subscription($sub_id, $cat_id) {
        $msg = RssHelpersSubscription::moveItem($sub_id, $cat_id);
        redirect("/rss/config");
    }

    public static function edit_subscription($id = null) {
        $user = HelpersUser::getCurrent();

        $subscription = RssHelpersSubscription::getItem($id);
        $categories = RssHelpersFeeds::getCategoryList($user->id);

        $template = ClassesTwig::getInstance();
        return $template->render("rss/views/ajax_subscription.twig", array(
            "subscription" => $subscription,
            "categories" => $categories
        ));
        }
}