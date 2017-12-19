<?php

class ControllersLogin {
    public static function home() {

        if (!empty($_POST)) {
            trace("test");
            die();
        }

        $template = ClassesTwig::getInstance();
        return $template->render("views/login.twig");
    }

    public static function logout() {
        HelpersUser::logout();
        redirect("/");
    }
}
