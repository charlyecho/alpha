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
            $db = ClassesDb::getInstance();

            // nb rss items unread
            $sql = "SELECT COUNT(si.id) FROM subscription_item si INNER JOIN subscription s ON s.id = si.subscription_id WHERE s.user_id = ".$db->quote($user->id);
            $s = $db->prepare($sql);
            $s->execute();
            $nb_rss = $s->fetch(PDO::FETCH_COLUMN);

            $sql = "SELECT COUNT(si.id) FROM subscription_item si INNER JOIN subscription s ON s.id = si.subscription_id WHERE si.read = 0 AND s.user_id = ".$db->quote($user->id);
            $s = $db->prepare($sql);
            $s->execute();
            $nb_unread = $s->fetch(PDO::FETCH_COLUMN);

            // nb links
            $sql = "SELECT COUNT(id) FROM link WHERE user_id = ".$db->quote($user->id);
            $s = $db->prepare($sql);
            $s->execute();
            $nb_links = $s->fetch(PDO::FETCH_COLUMN);

            $template = ClassesTwig::getInstance();
            return $template->render("home/views/home.twig", array(
                "nb_links" => $nb_links,
                "nb_unread" => $nb_unread,
                "nb_rss" => $nb_rss
            ));
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