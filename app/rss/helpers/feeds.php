<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 02/12/17
 * Time: 11:14
 */

class RssHelpersFeeds {
    public static function getCategoryList($user_id) {
        $db = ClassesDb::getInstance();
        $s = $db->prepare("SELECT * FROM category WHERE user_id = ".$db->quote($user_id)." ORDER BY name");
        $s->execute();
        return $s->fetchAll();
    }

    public static function getSubList($user_id) {
        $db = ClassesDb::getInstance();
        $sql = "SELECT s.* FROM subscription s WHERE s.user_id = ".$db->quote($user_id);
        $s = $db->prepare($sql);
        $s->execute();
        return $s->fetchAll();
    }

    public static function getNbSub($user_id) {
        $db = ClassesDb::getInstance();
        $s = $db->prepare("SELECT s.id, count(si.id) as nb FROM subscription s LEFT JOIN subscription_item si ON (si.subscription_id = s.id AND si.read = 0) WHERE s.user_id = ".$db->quote($user_id)." GROUP BY s.id");
        $s->execute();
        $list = $s->fetchAll(PDO::FETCH_KEY_PAIR);

        $sql = "SELECT count(si.id) FROM subscription_item si WHERE si.starred = 1";
        $s = $db->prepare($sql);
        $s->execute();
        $list["starred"] = $s->fetch(PDO::FETCH_COLUMN);
        return $list;
    }

    public static function getSubItems($user_id, $cat_id = null, $sub_id = null, $read = 0, $starred = 0, $nsfw = 0) {
        $db = ClassesDb::getInstance();
        $sql = "SELECT si.*, s.name as sub_name, s.url_site FROM subscription_item AS si INNER JOIN subscription s ON s.id = si.subscription_id ";
        $sql .= "WHERE s.user_id = ".$db->quote($user_id);
        if ($cat_id !== "") {
            if ($cat_id == 0) {
                $sql.= " AND s.category_id IS NULL";
            }
            else {
                $sql.= " AND s.category_id = ".$db->quote($cat_id);
            }
        }
        if ($sub_id) {
            $sql.= " AND s.id = ".$db->quote($sub_id);
        }
        if (!$starred) {
            $sql .= " AND si.read = " . $db->quote((int)$read);
        }
        if (!$nsfw) {
            $sql .= " AND s.is_mature = '0'";
        }
        $sql.= " AND si.starred = ".$db->quote((int) $starred);
        $sql .= " ORDER BY date_time DESC";
        if (!$starred) {
            $sql .= " LIMIT 0, 20";
        }

        $s = $db->prepare($sql);
        $s->execute();
        $data = $s->fetchAll();

        foreach($data as $d) {
            $d = self::cleanSub($d);
        }

        return $data;
    }


    public static function cleanSub($data) {
        // favicon
        $path = "app/cache/icons/".$data->subscription_id.".png";
        if (is_file($path)) {
            $data->favicon = $path;
        }
        elseif($data->url_site) {
            $data->favicon = "https://www.google.com/s2/favicons?domain=".urlencode($data->url_site);
            @file_put_contents($path, file_get_contents($data->favicon));
        }
        else {
            $data->favicon = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";
        }

        // cleanup imgs tags
        $imgs = "<img([\w\W]+?)[\/]?>";
        preg_match_all('/<img[^>]+>/i',$data->content, $img_found);
        foreach($img_found as $i) {
            $new_string = null;
            foreach($i as $_img) {
                $_old_image = $_img;
                $explode = explode(" ", $_img);
                foreach($explode as $_data) {
                    if (substr(trim($_data), 0, 4) == "src=") {
                        $attr = "";

                        $_data = str_replace("src=", "", $_data);
                        $_data = trim($_data, "\"'<>");
                        if (strpos($_data, ".gif") !== false) {
                            $attr .= " uk-gif";
                        }
                        $new_string = "<img data-lazy_src=\"$_data\" src=\"data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==\" alt=\"\" $attr />";

                        $data->content = str_replace($_old_image, $new_string, $data->content);
                    }
                }
            }
        }


        // bad <br> between 2 <p></p>
        //$data->content = nl2br($data->content);
        $regex = '/<br[ \/]*>[ \r\n]*(<p|<blockquote|<div)/';
        $data->content = preg_replace($regex, "$1", $data->content);

        // iframes
        //$data->content = str_replace("<iframe ", "<iframe ", $data->content);
        //$data->content = str_replace("</iframe>", "</iframe>", $data->content);

        // thumbnail
        if ($data->thumbnail && strpos($data->content, $data->thumbnail) === false) {
            if (strpos($data->link, "youtube.com") === false) {
                $data->content = "<p><img data-lazy_src='".$data->thumbnail."' alt='' src='data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' /></p>".$data->content;
            }
        }

        // audio ?
        if ($data->link && (strpos($data->content, $data->link) === false) && (strpos($data->link, ".mp3") !== false)) {
            $data->content .= "<audio style='width:100%;' controls src='".$data->link."'></audio>";
        }

        // title
        $data->title = html_entity_decode($data->title);

        return $data;
    }


    public static function getFeedTree($user_id) {
        $_categories = self::getCategoryList($user_id);
        $subs = self::getSubList($user_id);

        $categories = array();
        foreach($_categories as $c) {
            $c->subs = array();
            $categories[$c->id] = $c;
        }
        $unknown = new stdClass();
        $unknown->id = 0;
        $unknown->user_id = $user_id;
        $unknown->name = "- Uncategorized";
        $unknown->color = "#ffffff";
        $unknown->subs = array();
        $categories["unknown"] = $unknown;

        foreach($subs as $s) {
            if ($s->category_id) {
                $categories[$s->category_id]->subs[] = $s;
            }
            else {
                $categories["unknown"]->subs[] = $s;
            }
        }

        return $categories;
    }

    /**
     * import data from uploaded file for a user_id
     *
     * @param $user_id
     * @param $data
     */
    public static function import($user_id, $data) {
        $db = ClassesDb::getInstance();

        // cats
        $_cats = RssHelpersFeeds::getCategoryList($user_id);
        $cats_name_id = array();
        foreach($_cats as $_cat) {
            $cats_name_id[$_cat->name] = $_cat->id;
        }
        $cats_id_name = array_flip($cats_name_id);

        // subs
        $_subs = RssHelpersFeeds::getSubList($user_id);
        $subs_url_id = array();
        foreach($_subs as $s) {
            $subs_url_id[$s->url] = $s->id;
        }

        // processing data from imported file
        $cats = array();
        foreach($data->body->outline as $cat) {
            $_id = null;
            $cat_name = html_entity_decode($cat->{"@attributes"}->text);
            if (isset($cats_name_id[$cat_name])) {
                $_id = $cats_name_id[$cat_name];
            }
            else {
                $sql = "INSERT INTO category (name, user_id) VALUES (".$db->quote($cat_name).", ".$db->quote($user_id).")";
                $s = $db->prepare($sql);
                $s->execute();
                $_id = $db->lastInsertId();
            }

            // feeds
            if (isset($cat->outline)) {
                foreach ($cat->outline as $sub) {
                    if (isset($sub->{"@attributes"})) {
                        $feed = $sub->{"@attributes"};

                        $url = isset($feed->xmlUrl) ? $feed->xmlUrl : null;
                        if (isset($subs_url_id[$url])) {
                            continue; // existe déjà
                        }
                        $name = $feed->text;

                        $sql = "INSERT INTO subscription (name, url, user_id, category_id) VALUES (".$db->quote($name).", ".$db->quote($url).", ".$db->quote($user_id).", ".$db->quote($_id).")";
                        $s = $db->prepare($sql);
                        $s->execute();
                    }
                }
            }


            $cats[] = $cat_name;
        }
    }
}