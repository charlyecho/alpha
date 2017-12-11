<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 01/12/17
 * Time: 23:43
 */

class HelpersUser {
    private static $current_user = null;

    public static function session($id) {
        $session = ClassesSession::getInstance();
        $session->set("user_id", $id);
        redirect("/");
    }

    public static function getCurrent() {
        // session
        $session = ClassesSession::getInstance();
        if (!$user_id = $session->get("user_id")) {
            return false;
        }
        else {
            if (!self::$current_user) {
                self::$current_user = self::loadUser($user_id);
            }

            return self::$current_user;
        }
    }

    public static function loadUser($user_id) {
        $db = ClassesDb::getInstance();
        $s = $db->prepare("SELECT * FROM user WHERE id = ".$db->quote($user_id));
        $s->execute();
        return $s->fetch();
    }

    /**
     * check for user connected
     *
     * @return bool
     * @throws Exception
     */
    public static function checkConnected() {
        if (!$user = self::getCurrent()) {
            redirect("/login");
        }
        return $user;
    }

    public static function logout() {
        $session = ClassesSession::getInstance();
        $session->delete("user_id");
        $session->destroy();
    }
}