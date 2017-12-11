<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 01/12/17
 * Time: 21:32
 */

class ControllersFeeds {
    public static function home() {
        $user = HelpersUser::getCurrent();

        $tree = HelpersFeeds::getFeedTree($user->id);

        $template = ClassesTwig::getInstance();
        return $template->render("views/flow.twig", array(
            "tree" => $tree
        ));
    }

    public static function ajax() {
        $user = HelpersUser::getCurrent();
        $date = ClassesDate::getInstance()->toSql();

        $cat_id = get($_POST, "cat_id");
        $sub_id = get($_POST, "sub_id");
        $status = get($_POST, "status", array());   // status to update (read, starred)

        // subscriptions count unread
        $nb = json_encode(HelpersFeeds::getNbSub($user->id), JSON_OBJECT_AS_ARRAY);

        $items = HelpersFeeds::getSubItems($user->id, $cat_id, $sub_id);


        //@tODO : ajouter les status dans le formulaire via l'ajax

        $template = ClassesTwig::getInstance();
        return $template->render("views/ajax.twig", array(
            "count" => $nb,
            "items" => $items
        ));
    }

    /**
     * feed managment view
     *
     * @return string
     */
    public static function config() {
        $user = HelpersUser::getCurrent();

        $_categories = HelpersFeeds::getCategoryList($user->id);
        $subs = HelpersFeeds::getSubList($user->id);

        // remplissage cats
        $categories = array();

        $nstd = new stdClass();
        $nstd->id = null;
        $nstd->name = "sans categorie";
        $nstd->subs = array();
        $categories["nocat"] = $nstd;
        foreach($_categories as $c) {
            $c->subs = array();
            $categories[$c->id] = $c;
        }
        foreach($subs as $_sub) {
            if ($_sub->category_id) {
                $categories[$_sub->category_id]->subs[$_sub->id] = $_sub;
            }
            else {
                $categories["nocat"]->subs[0] = $_sub;
            }
        }

        $template = ClassesTwig::getInstance();
        return $template->render("views/config.twig", array(
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
        $tree = HelpersFeeds::getFeedTree($user->id);
        $date = ClassesDate::getInstance()->format("Y-m-d_H-i");

        header( 'Content-Type: text/xml; charset=UTF-8', true );
        header('Content-Disposition: attachment; filename="'.$date.'_export.opml"');

        $template = ClassesTwig::getInstance();
        return $template->render("views/export.twig", array(
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

        HelpersFeeds::import($user->id, $std);

    }

    public static function ajax_edit_category($id = null) {
        $user = HelpersUser::getCurrent();

        $category = HelpersCategory::getItem($id);
        $categories = HelpersFeeds::getCategoryList($user->id);

        $template = ClassesTwig::getInstance();
        return $template->render("views/ajax_category.twig", array(
            "category" => $category,
            "categories"=> $categories
        ));
    }

    public static function ajax_post_category() {
        $msg = HelpersCategory::editItem($_POST);
        redirect("/config");
    }

    public static function ajax_post_subscription() {
        $msg = HelpersSubscription::editItem($_POST);
        redirect("/config");
    }

    public static function move_subscription($sub_id, $cat_id) {
        $msg = HelpersSubscription::moveItem($sub_id, $cat_id);
        redirect("/config");
    }

    public static function edit_subscription($id = null) {
        $user = HelpersUser::getCurrent();

        $subscription = HelpersSubscription::getItem($id);
        $categories = HelpersFeeds::getCategoryList($user->id);


        $template = ClassesTwig::getInstance();
        return $template->render("views/ajax_subscription.twig", array(
            "subscription" => $subscription,
            "categories" => $categories
        ));
        }
}