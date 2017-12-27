<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 26/12/17
 * Time: 00:47
 */

class LinksControllersInfo {
    public static function info()
    {
        $data = new stdClass();
        $data->img = null;
        $data->title = null;
        $data->url = null;
        $data->type = "link";

        // url
        $url = get($_GET, "url");
        $url = urldecode($url);
        $data->url = $url;

        // dom
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $content_type = null;
        foreach(explode("\n", $header) as $h) {
            if (strpos(strtolower(trim($h)), "content-type") === 0) {
                $content_type = $h;
            }
        }
        if ($content_type) {
            $content_type = explode(":", $content_type)[1];
            $content_type = trim($content_type);
        }
        $body = substr($response, $header_size);

        if (strpos($content_type, "html") !== false) {
            $domd = new DOMDocument();
            $domd->validateOnParse = true;
            $html = @$domd->loadHTML($body);
            if ($html === true) {
                // title
                $title = $domd->getElementsByTagName("title")->item(0)->textContent;
                $data->title = $title;

                // image
                $metas = $domd->getElementsByTagName("meta");
                foreach ($metas as $m) {
                    $property = $m->getAttribute('property');
                    $content = $m->getAttribute('content');

                    if ($property == 'og:image') {
                        if (!$data->img) {
                            $data->img = $content;
                        }
                    }

                    if ($property == 'og:type') {
                        if (strpos($content, "music") !== false) {
                            $data->type = "audio";
                        }
                        elseif (strpos($content, "video") !== false) {
                            $data->type = "video";
                        }
                        elseif (strpos($content, "image") !== false) {
                            $data->type = "image";
                        }
                        else {
                            $data->type = "link";
                        }
                    }
                }
            }
        }
        else {
            // nothin
            $explode_point = explode(".", $url);
            $last_point = end($explode_point);
            if (in_array($last_point, array("jpg", "png", "svg", "jpeg", "tiff"))) {
                $data->type = "image";
                $data->img = $url;
                $data->title = "Image";
            }
            if (in_array($last_point, array("gif", "mp4"))) {
                $data->type ="video";
                $data->title = "Video";
                if (!$data->img && $last_point == "gif") {
                    $data->img = $url;
                }
            }
        }

        echo json_encode($data);
    }
}