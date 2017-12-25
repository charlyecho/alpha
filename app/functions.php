<?php
/**
 * User: charly
 * Date: 01/12/17
 * Time: 21:30
 */

/**
 * print the variable friendy
 *
 * @param $data
 */
function trace($data) {
    echo "<pre style='border:solid 1px black;'>";
    print_r($data);
    echo "</pre>";
}


function redirect($target) {
    $prefix = isset($_SERVER["DOCUMENT_URI"]) ? str_replace("/index.php", "", $_SERVER["DOCUMENT_URI"]) : null;
    $uri = $prefix.$target;
    header("Location: ".$uri);
    exit();
}

function get($var, $key, $default = null) {
    return isset($var[$key]) ? $var[$key] : $default;
}

/**
 * @param $path
 * @throws Exception
 */
function autoload($path) {
    $list = preg_split('/(?=[A-Z])/', $path, -1, PREG_SPLIT_NO_EMPTY);
    $_path = implode(DIRECTORY_SEPARATOR, $list);
    $_path = strtolower(__DIR__."/".$_path).".php";
    if (is_file($_path)) {
        include_once $_path;
        return;
    }

    //throw new Exception("Impossible de charger la classe ".$path." dans ".$_path);

}
spl_autoload_register('autoload');