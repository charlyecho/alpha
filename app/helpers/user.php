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
        if ($id) {
            $session->set("user_id", $id);
        }
        else {
            $session->destroy();
        }
        redirect("/");
    }

    public static function getCurrent() {
        if (!is_file(__DIR__."/../db/db.sqlite")) {
            ClassesSession::getInstance()->delete("user_id");
            if (ClassesTwig::$route != "/install") {
                redirect("/install");
            }
        }

        if (self::$current_user) {
            return self::$current_user;
        }

        // by current_user (server)
        $remote_user = isset($_SERVER["REMOTE_USER"]) ? $_SERVER["REMOTE_USER"] : null;
        if ($remote_user) {
            $db = ClassesDb::getInstance();
            $sql = "SELECT * FROM user WHERE name = " . $db->quote($remote_user);
            $s = $db->prepare($sql);
            $s->execute();
            $_user = $s->fetch();
            if ($_user) {
                self::$current_user = $_user;
                return self::$current_user;
            }

            // create one
            $sql = "INSERT INTO user (name) VALUES (".$db->quote($remote_user).")";
            $s = $db->prepare($sql);
            $s->execute();
            $sql = "SELECT * FROM user WHERE name = " . $db->quote($remote_user);
            $s = $db->prepare($sql);
            $s->execute();
            $_user = $s->fetch();
            self::$current_user = $_user;
            return self::$current_user;

        }


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
        try {
            $db = ClassesDb::getInstance();
            $s = $db->prepare("SELECT * FROM user WHERE id = " . $db->quote($user_id));
            $s->execute();
        }
        catch (Exception $e) {
            ClassesSession::getInstance()->delete("user_id");
            redirect("/install");
        }
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