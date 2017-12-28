<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 01/12/17
 * Time: 21:32
 */

class HomeControllersHome {
    public static function home() {
        if ($user = HelpersUser::getCurrent()) {

            // nb rss items unread

            // nb links

            $template = ClassesTwig::getInstance();
            return $template->render("home/views/home.twig", array());
        }

        $path_db = __DIR__."/../../db/db.sqlite";
        if (!is_file($path_db)) {
            redirect("/install");
        }

        redirect("/login");
    }

    public static function preferences() {
        $template = ClassesTwig::getInstance();
        return $template->render("template/views/preferences.twig", array());
    }
}