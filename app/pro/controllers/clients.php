<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 07/01/18
 * Time: 14:38
 */

class ProControllersClients {
    public static function home() {

        $user = HelpersUser::getCurrent();

        $list = ProHelpersClients::getList($user->id);

        $template = ClassesTwig::getInstance();
        return $template->render("pro/views/clients.twig", array(
            "list" => $list
        ));
    }
}