<?php

class ControllersLogin {
    public static function home() {

        $session = ClassesSession::getInstance();
        if ($session->get("user_id")) {
            redirect("/");
        }

        $remote_user = isset($_SERVER["REMOTE_USER"]) ? $_SERVER["REMOTE_USER"] : null;

        if ($remote_user) {
            $user_id = null;
            $db = ClassesDb::getInstance();
            $sql = "SELECT * FROM user WHERE name = ".$db->quote($remote_user);
            $s = $db->prepare($sql);
            $s->execute();
            $user = $s->fetch();

            if (!$user) {
                // create one
                $sql = "INSERT INTO user (name) VALUES (".$db->quote($remote_user).")";
                $s = $db->prepare($sql);
                $s->execute();
                $user_id = $db->lastInsertId();

            }
            else {
                $user_id = $user->id;
            }

            $session->set("user_id", $user_id);
            redirect("/");
        }


        $template = ClassesTwig::getInstance();
        return $template->render("views/login.twig");
    }

    public static function logout() {
        HelpersUser::logout();
        redirect("/");
    }
}
