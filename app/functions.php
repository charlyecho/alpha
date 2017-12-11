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
    header("Location: ".$target);
    exit();
}

function get($var, $key, $default = null) {
    return isset($var[$key]) ? $var[$key] : $default;
}

function createDb() {
    $path = __DIR__.'/db/db.sqlite';
    $database = new SQLite3($path);
    $db = ClassesDb::getInstance();

    $script = file_get_contents(__DIR__ . "/db/create_db.txt");
    $script = str_replace("\n", "", $script);
    $statements = array_filter(explode(";", $script));
    foreach($statements as $key => $query) {
        try {
            $s = $db->prepare($query);
            $s->execute();
        }
        catch (Exception $e) {
            trace($e->getMessage()." ".($query));
            die();
        }
    }

    redirect("/");
}

/**
 * @param $path
 * @throws Exception
 */
function autoload($path) {
    $list = preg_split('/(?=[A-Z])/', $path, -1, PREG_SPLIT_NO_EMPTY);
    $_path = implode(DIRECTORY_SEPARATOR, $list);
    $_path = strtolower("app".DIRECTORY_SEPARATOR.$_path).".php";
    if (is_file($_path)) {
        include_once $_path;
        return;
    }

    //throw new Exception("Impossible de charger la classe ".$path." dans ".$_path);

}
spl_autoload_register('autoload');