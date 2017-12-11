<?php

class ControllersLogin {
    public static function home() {
        $template = ClassesTwig::getInstance();
        return $template->render("views/login.twig");
    }

    public static function logout() {
        HelpersUser::logout();
        redirect("/");
    }
}
