<?php

class LinksControllersHome {
    public static function home() {
        $user = HelpersUser::getCurrent();

        $list = LinksHelpersLinks::getList($user->id);

        $template = ClassesTwig::getInstance();
        return $template->render("links/views/home.twig", array(
            "links" => $list
        ));
    }
}