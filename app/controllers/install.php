<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 11/12/17
 * Time: 21:21
 */

class ControllersInstall {

    public static function updateVersion($v = null) {
        $db = ClassesDb::getInstance();
        $sql = "SELECT val FROM app WHERE param = 'db_version'";
        $s = $db->prepare($sql);
        $s->execute();
        $current_version = $s->fetch(PDO::FETCH_COLUMN);

        if ($v) {
            $sql = "UPDATE app SET param = 'db_version', val=".$db->quote($v);
            $s = $db->prepare($sql);
            $s->execute();
            return $v;
        }

        return $current_version;
    }

    public static function home() {
        $report = array();

        // cache writable
        $writable_icons = is_writable(__DIR__."/../cache/icons/");
        $writable_rss = is_writable(__DIR__."/../cache/rss/");
        $nb_errors = 0;


        if (!is_dir(__DIR__."/../cache")) {
            if ($create_cache = mkdir(__DIR__."/../cache")) {
                $report[] = array(
                    "type" => "success",
                    "msg" => "cache directory created");
            }
            else {
                $report[] = array(
                    "type" => "danger",
                    "msg" => "unable to create cache directory");
            }
        }
        else {
            $report[] = array(
                "type" => "success",
                "msg" => "cache directory already created");
        }

        if (!is_dir(__DIR__."/../cache/icons")) {
            if ($create_cache = mkdir(__DIR__."/../cache/icons")) {
                $report[] = array(
                    "type" => "success",
                    "msg" => "cache/icons directory created");
            }
            else {
                $report[] = array(
                    "type" => "danger",
                    "msg" => "unable to create cache/icons directory");
            }
        }
        else {
            $report[] = array(
                "type" => "success",
                "msg" => "cache/icons directory already created");
        }

        if (!is_dir(__DIR__."/../cache/rss")) {
            if ($create_cache = mkdir(__DIR__."/../cache/rss")) {
                $report[] = array(
                    "type" => "success",
                    "msg" => "cache/rss directory created");
            }
            else {
                $report[] = array(
                    "type" => "danger",
                    "msg" => "unable to create cache/rss directory");
            }
        }
        else {
            $report[] = array(
                "type" => "success",
                "msg" => "cache/rss directory already created");
        }



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
            $db = ClassesDb::getInstance();

            $current_version = "0";
            try {
                $sql = "SELECT val FROM app WHERE param = ".$db->quote("db_version");
                $s = $db->prepare($sql);
                $s->execute();
                $db_version = $s->fetch(PDO::FETCH_COLUMN);
                if ($db_version) {
                    $current_version = $db_version;
                }
            } catch (Exception $e) {}

            $deployment = include(__DIR__."/../tools/deployment.php");

            foreach($deployment as $version => $requests) {
                if ($current_version >= $version) {
                    continue;
                }

                if (count($requests)) {
                    foreach ($requests as $r) {
                        try {
                            $db->exec($r);
                        } catch (Exception $e) {
                            $report[] = array(
                                "type" => "danger",
                                "msg" => "Error in version $version : ".$e->getMessage()
                            );
                            break 2;
                        }
                    }
                    $current_version = self::updateVersion($version);
                }
            }

            $report[] = array(
                "type" => "success",
                "msg" => "DB version : ".$current_version
            );
        }

        if (!$nb_errors) {
            //redirect("/");
        }


        $template = ClassesTwig::getInstance();
        return $template->render("template/views/install.twig", array(
            "report" => $report
        ));
    }

}