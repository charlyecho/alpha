<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 03/12/17
 * Time: 23:21
 */

use CERss\FeedParser;

class HelpersCron {

    public static function test() {
        $step = isset($_GET["step"]) ? $_GET["step"] : 0;
        $_id = isset($_GET["id"]) ? $_GET["id"] : 0;
        $db = ClassesDb::getInstance();
        if ($_id) {
            $sql = "SELECT * FROM subscription WHERE id = ".$db->quote($_id);
        }
        else {
            $sql = "SELECT * FROM subscription";
        }
        $s = $db->prepare($sql);
        $s->execute();
        $feeds = $s->fetchAll();

        if (!count($feeds)) {
            return;
        }

        if (count($feeds) > 1) {
            foreach ($feeds as $f) {
                echo "<a href=\"?id=".$f->id."\">" .$f->name."</a><br/>";
            }
            return;
        }


        $feed = reset($feeds);


        $url = $feed->url;
        $id = $feed->id;



        $_md5 = md5($url);
        $path = "app/cache/rss/" . $_md5 . ".xml";
        trace($url);
        trace($_md5);

        echo "STEP  ? : <a href=\"?id=".$id."&step=1\">check</a>, <a href=\"?id=".$id."&step=2\">Récup</a>, <a href=\"?id=".$id."&step=3\">Parse</a>";

        if ($_id) {


            if ($step == 1) {
                $mh = curl_init($url);
                curl_setopt($mh, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($mh, CURLOPT_FILETIME, true);
                curl_setopt($mh, CURLOPT_USERAGENT, 'Romeo Sierra Sierra Feed agent 0.5 by charlyecho (charlyecho.com/perso)');
                curl_setopt($mh, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($mh, CURLOPT_HEADER, TRUE);
                curl_setopt($mh, CURLOPT_NOBODY, true);
                curl_setopt($mh, CURLOPT_TIMEOUT, 30);
                curl_setopt($mh, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($mh, CURLOPT_COOKIESESSION, TRUE);
                $content = curl_exec($mh);
                $httpCode = curl_getinfo($mh, CURLINFO_HTTP_CODE);
                curl_close($mh);
            }

            if ($step == 2) {
                $mh = curl_init($url);
                curl_setopt($mh, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($mh, CURLOPT_USERAGENT, 'Romeo Sierra Sierra Feed agent 0.5 by charlyecho (charlyecho.com/perso)');
                curl_setopt($mh, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($mh, CURLOPT_HEADER, FALSE);
                curl_setopt($mh, CURLOPT_TIMEOUT, 30);
                curl_setopt($mh, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($mh, CURLOPT_COOKIESESSION, TRUE);
                $content = curl_exec($mh);
                $httpCode = curl_getinfo($mh, CURLINFO_HTTP_CODE);
                curl_close($mh);
                trace($httpCode);
            }

            if ($step == 3) {
                $date_month = ClassesDate::getInstance()->modify('-40 DAYS')->toSql();

                if (is_file($path)) {
                    $feed = FeedParser::parseFile($path);
                    //trace($feed);
                    foreach($feed->feed_items as $item) {
                        $_date = ClassesDate::getInstance($item->date_modification);
                        $_date->setTimezone(new DateTimeZone('UTC'));
                        trace($item->date_modification." ".$_date->toSql());
                        if ($_date <= $date_month) {
                            trace("too old");
                        }
                    }
                } else {
                    trace("PAS DE FICHIER");
                }
            }
        }
    }


    public static function cleanOld() {
        $date = ClassesDate::getInstance()->modify("-41 DAYS")->toSql();
        $db = ClassesDb::getInstance();

        $sql = "DELETE FROM subscription_item WHERE date_time <= '$date'";
        $s = $db->prepare($sql);
        $s->execute();
    }

    /**
     * get update for the subscription by getting the head (STEP 1)
     */
    public static function checkLastModification($sub_id = null) {
        $report = array();
        $db = ClassesDb::getInstance();
        $today = ClassesDate::getInstance()->toSql();

        // limit date to check 20 minutes !
        $a = ClassesDate::getInstance()->modify("-20 MINUTES")->toSql();

        if ($sub_id) {
            $sql = "SELECT * FROM subscription WHERE id = ".$db->quote($sub_id);
        }
        else {
            $sql = "SELECT * FROM subscription WHERE is_valid = 1 AND (last_check_datetime <= ".$db->quote($a)." OR last_check_datetime IS NULL) ORDER BY last_check_datetime  ASC LIMIT 0,40";
        }
        $s = $db->prepare($sql);
        $s->execute();
        $feeds = $s->fetchAll();

        $report[] = count($feeds)." subscriptions found";

        $multihandler = curl_multi_init();
        $handlers = $result = $status_co = array();

        foreach ($feeds as $key => $data) {
            $handlers[$key] = curl_init($data->url);
            $status_co[(int) $handlers[$key]] = null;

            curl_setopt($handlers[$key], CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($handlers[$key], CURLOPT_FILETIME, true);
            curl_setopt($handlers[$key], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.7) Gecko/20070914 Firefox/2.1.0.7');
            curl_setopt($handlers[$key], CURLOPT_CONNECTTIMEOUT, 40);
            curl_setopt($handlers[$key], CURLOPT_HEADER, TRUE);
            curl_setopt($handlers[$key], CURLOPT_NOBODY, true);
            curl_setopt($handlers[$key], CURLOPT_TIMEOUT, 55);
            curl_setopt($handlers[$key], CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($handlers[$key], CURLOPT_COOKIESESSION, TRUE);
            curl_multi_add_handle($multihandler, $handlers[$key]);
        }

        // init
        $pendingConnex = null;
        do {
            curl_multi_exec($multihandler, $pendingConnex);

            if (($info = curl_multi_info_read($multihandler)) !== false) {
                $status_co[(int) $info['handle']] = $info;
            }
            usleep(10000); // 10 ms
        } while ($pendingConnex > 0);

        // get content
        foreach ($feeds as $key => $data) {
            $content = curl_multi_getcontent($handlers[$key]);

            // everything ok ? check status
            $data->is_valid = 0;
            $status = $status_co[(int) $handlers[$key]];
            if ($status !== false) {
                $data->status_code = $status["result"];
            }

            $filetime = null;

            // ou essayer par last_modified
            if (!$filetime) {
                foreach (explode("\r\n", trim($content)) as $t) {
                    $cleaned_string = trim(strtolower($t));

                    if (strpos($cleaned_string, "last-modified") !== false) {
                        $filetime = substr($cleaned_string, strpos($cleaned_string, ":")+1);
                        $filetime = strtotime(trim($filetime));
                    }
                }
            }

            if (!$filetime) {
                // récupérer la dernier modif par le filetime (si il est valide)
                $info = curl_getinfo($handlers[$key]);
                $filetime = (isset($info["filetime"]) && $info["filetime"] > 0) ? $info["filetime"] : null;
            }

            if (!$filetime) {
                $filetime = time();
            }

            $filetime_dt = $filetime ? ClassesDate::getInstance($filetime)->toSql() : null;
            $data->filemtime = $filetime_dt;


            $data->is_valid = (int) ($data->status_code == CURLE_OK);
        }

        // close connexion
        foreach ($feeds as $key => $i) {
            curl_multi_remove_handle($multihandler, $handlers[$key]);
        }
        curl_multi_close($multihandler);

        $nb = 0;

        // update
        foreach($feeds as $f) {
            $need_update = $f->is_valid && (!$f->last_modification_datetime || $f->last_modification_datetime != $f->filemtime);
            if ($need_update) {
                $nb++;
            }
            //$need_update = (!$f->last_modification_datetime || $f->last_modification_datetime != $f->filemtime);

            $sql = "UPDATE subscription SET last_modification_datetime = ".$db->quote($f->filemtime).", is_valid = ".$db->quote($f->is_valid).", last_check_datetime = ".$db->quote($today).", need_update = ".$db->quote($need_update ? "1" : "0")." WHERE id = ".$db->quote($f->id);
            $q = $db->prepare($sql);
            if (!$q->execute()) {
                trace($q->errorInfo());
            }

            $report[] = "[".$f->id."] dt :  ".$f->filemtime." | valide : ".$f->is_valid." | need_update : ".(int) $need_update;
        }
        return $report;
    }

    /**
     * get rss feed data from websites (if need update)
     *
     * @return array messages
     */
    public static function getData($sub_id = null) {
        $report = array();
        $db = ClassesDb::getInstance();
        $today = ClassesDate::getInstance()->toSql();

        if ($sub_id) {
            $sql = "SELECT id, url FROM subscription WHERE id = ".$db->quote($sub_id);
        }
        else {
            $sql = "SELECT id, url FROM subscription WHERE /*is_valid = '1' AND*/ need_update = '1' ORDER BY id";
        }
        $s = $db->prepare($sql);
        $s->execute();
        $feeds = $s->fetchAll(PDO::FETCH_KEY_PAIR);

        $report[] = count($feeds)." subscriptions needed to get data";


        $multihandler = curl_multi_init();
        $handlers = $result = array();

        foreach ($feeds as $key => $url) {
            $handlers[$key] = curl_init($url);
            curl_setopt($handlers[$key], CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($handlers[$key], CURLOPT_USERAGENT, 'Romeo Sierra Sierra Feed agent 0.5 by charlyecho (charlyecho.com/perso)');
            curl_setopt($handlers[$key], CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($handlers[$key], CURLOPT_HEADER, FALSE);
            curl_setopt($handlers[$key], CURLOPT_TIMEOUT, 30);
            curl_setopt($handlers[$key], CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($handlers[$key], CURLOPT_COOKIESESSION, TRUE);
            curl_multi_add_handle($multihandler, $handlers[$key]);
        }

        $pendingConnex = null;
        do {
            $status = curl_multi_exec($multihandler, $pendingConnex);
            $info = curl_multi_info_read($multihandler);
            usleep(10000); // 10 ms
        } while ($pendingConnex > 0);

        foreach ($feeds as $key => $url) {
            $md5 = md5($url);
            $content = curl_multi_getcontent($handlers[$key]);

            $sql = "UPDATE subscription SET ";

            if ($content) {
                if (strpos($content, "xml") !== false) {
                    $sql .= "is_valid = '1', need_parse = '1'";
                    $report[] = "flux $key ($url), OK";
                    if (!file_put_contents(__DIR__."/../cache/rss/" . $md5 . ".xml", $content)) {
                        continue;
                    }
                }
                else {
                    $sql .= "is_valid = '0', need_parse = '0'";
                    $report[] = "flux $key ($url) pas RSS, désactivé";
                }
            }
            else {
                $sql .= "is_valid = '0', need_parse = '0'";
                $report[] = "flux $key ($url) vide, désactivé";
                continue;
            }
            $sql .= ", need_update= '0', recuperation_data_datetime = ".$db->quote($today);
            $sql .= " WHERE id = ".$db->quote($key);
            $s = $db->prepare($sql);
            $s->execute();
        }

        foreach ($feeds as $key => $i) {
            curl_multi_remove_handle($multihandler, $handlers[$key]);
        }
        curl_multi_close($multihandler);

        return $report;
    }

    /**
     * parse data
     *
     * @param null $sub_id
     * @return stdClass report
     */
    public static function parse($sub_id = null) {
        $report = new stdClass();
        $report->log = array();
        $report->nb_new = 0;


        // @TODO : use the feeds with filter 'to_parse' only

        $feed_id = $sub_id;

        $date_month = ClassesDate::getInstance()->modify('-40 DAYS')->toSql();

        $db = ClassesDb::getInstance();

        // all item_id
        $sql = "SELECT local_id FROM subscription_item";
        $s = $db->prepare($sql);
        $s->execute();
        $existing_items = $s->fetchAll(PDO::FETCH_COLUMN);

        // feeds == abos
        $sql = "SELECT id, url FROM subscription";
        $s = $db->prepare($sql);
        $s->execute();
        $feeds = $s->fetchAll(PDO::FETCH_KEY_PAIR);

        $url_to_id = array();
        foreach($feeds as $id => $url) {
            $url_to_id[md5($url)][] = $id;
        }

        $pattern = "app/cache/rss/*.xml";
        if ($feed_id) {
            $_url = $feeds[$feed_id];
            $pattern = __DIR__."/../../app/cache/rss/".md5($_url).".xml";
        }
        $files = glob($pattern);
        foreach($files as $f) {
            $nb_new_item = 0;
            $ids = explode("/", $f);
            $url = str_replace(".xml", "", end($ids));
            $id = isset($url_to_id[$url]) ? $url_to_id[$url][0] : null;

            // not found
            if (!$id || !isset($feeds[$id])) {
                unlink($f);
                $report[] = "Flux supprimé du cache (non trouvé)";
                continue;
            }

            $feed = FeedParser::parseFile($f);
            // items
            foreach ($feed->feed_items as $key => $_item) {

                // already stored
                if (in_array($_item->guid, $existing_items)) {
                    continue;
                }

                try {
                    $_date = ClassesDate::getInstance($_item->date_modification)->setTimezone(new DateTimeZone('UTC'))->toSql();
                }
                catch (Exception $e) {
                    continue;
                }

                // too old (40 days)
                if ($_date <= $date_month) {
                    continue;
                }

                $thumbnail = null;
                if (count($_item->enclosures)) {
                    $thumbnail = reset($_item->enclosures);
                }

                $nb_new_item++;
                $report->nb_new ++;

                // fill the items
                foreach($url_to_id[$url] as $id) {
                    $sql = "INSERT INTO subscription_item (local_id, subscription_id, title, thumbnail, content, link, date_time) ";
                    $sql .= "VALUES (".$db->quote($_item->guid).", ".$db->quote($id).",".$db->quote($_item->title).", ".$db->quote($thumbnail).", ".$db->quote($_item->text).", ".$db->quote($_item->link).", ".$db->quote($_date).")";
                    try {
                        $s = $db->prepare($sql);
                        if (!$s->execute()) {
                            trace($s->errorInfo());
                            trace($_item);
                        }
                    }
                    catch (Exception $e) {
                        trace($e->getMessage());
                        trace($_item);
                        continue;
                    }
                }

            }

            if ($nb_new_item) {
                $report->log[] = $feeds[$id] . " => " . $nb_new_item;
            }

            // update website url
            if ($feed->feed_link) {
                foreach ($url_to_id[$url] as $_id) {
                    $sql = "UPDATE subscription SET url_site = " . $db->quote($feed->feed_link)." WHERE id = ".$db->quote($_id);
                    $s = $db->prepare($sql);
                    $s->execute();
                }
            }
        }

        return $report;
    }

    public static function parse2($id = null) {
        $date_month = ClassesDate::getInstance()->modify('-40 DAYS')->toSql();
        $report = array();
        $db = ClassesDb::getInstance();
        $nb = 0;
        // get all subscriptions to parse
        if ($id) {
            $sql = "SELECT * FROM subscription WHERE id = ".$db->quote($id);
        }
        else {
            $sql = "SELECT * FROM subscription WHERE need_parse = '1'";
        }
        $s = $db->prepare($sql);
        $s->execute();
        $feeds = $s->fetchAll();
        $_ids = array();
        foreach($feeds as $f) {
            $_ids[] = $f->id;
        }
        $report[] = count($feeds)." subscriptions to parse";

        // items already stored for those subscriptions
        $sql = "SELECT local_id FROM subscription_item WHERE subscription_id IN ('".implode("','", $_ids)."')";
        $s = $db->prepare($sql);
        $s->execute();
        $existing_items = $s->fetchAll(PDO::FETCH_COLUMN);

        foreach($feeds as $f) {
            $id = $f->id;
            $md5_link = md5($f->url);
            $file = __DIR__."/../../app/cache/rss/$md5_link.xml";

            $feed = FeedParser::parseFile($file);
            foreach ($feed->feed_items as $key => $_item) {
                // already stored
                if (in_array($_item->guid, $existing_items)) {
                    continue;
                }

                try {
                    $_date = ClassesDate::getInstance($_item->date_modification)->setTimezone(new DateTimeZone('UTC'))->toSql();
                }
                catch (Exception $e) {
                    continue;
                }

                // too old (40 days)
                if ($_date <= $date_month) {
                    continue;
                }

                if (!trim($_item->link) && $_item->guid) {
                    $_item->link = filter_var($_item->guid, FILTER_VALIDATE_URL);
                }

                $thumbnail = null;
                if (count($_item->enclosures)) {
                    foreach($_item->enclosures as $_enc) {
                        $ext = explode(".", $_enc);
                        if (count($ext)) {
                            $ext = end($ext);
                            if (in_array($ext, array("jpg", "jpeg", "png", "gif", "svg"))) {
                                $thumbnail = $_enc;
                                break;
                            }
                            if (in_array($ext, array("mp3", "ogg", "wav"))) {
                                if (strpos($_item->text, $_enc) === false) {
                                    $_item->text .= "<audio style='width:100%;' controls src='".$_enc."'></audio>";
                                }
                            }
                        }
                    }
                }

                // fill the items
                $sql = "INSERT INTO subscription_item (local_id, subscription_id, title, thumbnail, content, link, date_time) ";
                $sql .= "VALUES (".$db->quote($_item->guid).", ".$db->quote($id).",".$db->quote($_item->title).", ".$db->quote($thumbnail).", ".$db->quote($_item->text).", ".$db->quote($_item->link).", ".$db->quote($_date).")";
                try {
                    $s = $db->prepare($sql);
                    if (!$s->execute()) {
                        trace($s->errorInfo());
                        trace($_item);
                    }
                    $nb++;
                }
                catch (Exception $e) {
                    trace($e->getMessage());
                    trace($_item);
                    continue;
                }
            }

            // disable parsing
            $sql = "UPDATE subscription SET need_parse = '0' WHERE id =".$db->quote($id);
            $s = $db->prepare($sql);
            $s->execute();

            $report[] = $file;
        }

        return $nb;
    }
}