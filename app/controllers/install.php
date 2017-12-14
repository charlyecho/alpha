<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 11/12/17
 * Time: 21:21
 */

class ControllersInstall {
    public static function home() {
        $report = array();


        // cache writable
        $writable_icons = is_writable(__DIR__."/../cache/icons/");
        $writable_rss = is_writable(__DIR__."/../cache/rss/");

        $report[] = array(
            "type" => !$writable_icons ? "danger" : "success",
            "msg" => !$writable_icons ? "cache/icons folder is not writable" : "cache/icons folder is writable");

        if ($writable_icons) {
            $icons = glob(__DIR__."/../cache/icons/*.png");
            if (count($icons)) {
                foreach ($icons as $_icon) {
                    unlink($_icon);
                }
                $report[] = array(
                    "type" => "success",
                    "msg" => "Icons cleaned");
            }
        }

        $report[] = array(
            "type" => !$writable_rss ? "danger" : "success",
            "msg" => !$writable_rss ? "cache/rss folder is not writable" : "cache/rss folder is writable");

        if ($writable_rss) {
            $rss = glob(__DIR__."/../cache/rss/*.xml");
            if (count($rss)) {
                foreach ($rss as $_xml) {
                    unlink($_xml);
                }
                $report[] = array(
                    "type" => "success",
                    "msg" => "Xml files cleaned");
            }
        }

        $db = __DIR__."/../db/";

        // db writable
        $writable = is_writable($db);
        $report[] = array(
            "type" => !$writable ? "danger" : "success",
            "msg" => !$writable ? "app/db folder is not writable" : "app/db folder is writable");

        // fill db
        if ($writable) {
            $database = new SQLite3($db."db.sqlite", SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            $db = ClassesDb::getInstance();

            // user
            $sql = "CREATE TABLE user(id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, name VARCHAR UNIQUE);";
            if ($db->exec($sql)) {
                $report[] = array(
                    "type" => "success",
                    "msg" => "Table 'user' created");
            }

            // add user
            $sql = "INSERT INTO user(name) VALUES ('admin');";
            if ($db->exec($sql)) {
                $report[] = array(
                    "type" => "success",
                    "msg" => "user admin created");
            }

            // category
            $sql = "CREATE TABLE category(id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,  user_id INTEGER CONSTRAINT category_user_id_fk REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE,  name    VARCHAR,  color   VARCHAR(6));";
            if ($db->exec($sql)) {
                $report[] = array(
                    "type" => "success",
                    "msg" => "Table 'category' created");
            }

            // subscription
            $sql ="CREATE TABLE subscription(id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, name VARCHAR, url TEXT, user_id INTEGER NOT NULL CONSTRAINT subscription_user_id_fk REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE,  category_id INTEGER CONSTRAINT subscription_category_id_fk REFERENCES category (id), last_modification_datetime DATETIME, need_update                ENUM(0, 1) DEFAULT 0, last_check_datetime        DATETIME, recuperation_data_datetime DATETIME, is_valid ENUM(0, 1) DEFAULT 1, url_site TEXT,  need_parse ENUM(0, 1) DEFAULT 0 NOT NULL);";
            if ($db->exec($sql)) {
                $report[] = array(
                    "type" => "success",
                    "msg" => "Table 'subscription' created");
            }

            // subscription item
            $sql = "CREATE TABLE subscription_item (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, subscription_id INTEGER CONSTRAINT subscription_item_subscription_id_fk REFERENCES subscription (id) ON UPDATE CASCADE ON DELETE CASCADE, local_id VARCHAR, title VARCHAR, thumbnail VARCHAR, content TEXT, link TEXT, date_time DATETIME, read ENUM(0, 1) DEFAULT 0, starred ENUM(0,1) DEFAULT 0);";
            if ($db->exec($sql)) {
                $report[] = array(
                    "type" => "success",
                    "msg" => "Table 'subscription_item' created");
            }

            $sql = "DELETE FROM subscription_item";
            if ($db->exec($sql)) {
                $report[] = array(
                    "type" => "success",
                    "msg" => "Table 'subscription_item' cleaned (truncated)");
            }
        }



        $template = ClassesTwig::getInstance();
        return $template->render("views/install.twig", array(
            "report" => $report
        ));

    }

}