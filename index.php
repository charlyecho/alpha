<?php
/**
 * Hi :)
 */
ob_start();
ini_set('default_charset', 'UTF-8');
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
date_default_timezone_set("UTC");

require_once __DIR__.'/app/functions.php';
require_once __DIR__.'/vendor/autoload.php';

// routing
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $path_db = __DIR__."/app/db/db.sqlite";

    $r->addRoute("GET", "/", array("HomeControllersHome", "home"));
    $r->addRoute("GET", "/install", array("ControllersInstall", "home"));
    $r->addRoute(array("GET", "POST"), "/login", array("ControllersLogin", "home"));

    if (is_file($path_db)) {
        $r->addRoute("GET", "/logout", array("ControllersLogin", "logout"));
        $r->addRoute("GET", "/session/{id:\d+}", array("HelpersUser", "session"));


        $r->addRoute("GET", "/cron/step1", array("HelpersCron", "checkLastModification"));
        $r->addRoute("GET", "/cron/step2", array("HelpersCron", "getData"));
        $r->addRoute("GET", "/cron/step3", array("HelpersCron", "parse"));
        $r->addRoute("GET", "/update", array("ControllersCron", "update"));

        if (HelpersUser::getCurrent()) {
            $r->addRoute(array("GET", "POST"), "/preferences", array("HomeControllersHome", "preferences"));

            // RSS
            $r->addGroup('/rss', function (FastRoute\RouteCollector $r) {
                $r->addRoute("GET", "/home", array("RssControllersFeeds", "home"));
                $r->addRoute("GET", "/feeds", array("RssControllersFeeds", "home"));
                $r->addRoute("POST", "/ajax_flow", array("RssControllersFeeds", "ajax"));
                $r->addRoute("GET", "/debug/{id:\d+}", array("ControllersCron", "debug"));

                // Config
                $r->addGroup('/config', function (FastRoute\RouteCollector $r) {
                    $r->addRoute('GET', '', array("RssControllersFeeds", "config"));
                    $r->addRoute('GET', '/export.opml', array("RssControllersFeeds", "export"));
                    $r->addRoute('POST', '/import', array("RssControllersFeeds", "import"));
                    $r->addGroup('/category', function (FastRoute\RouteCollector $r) {
                        $r->addRoute("GET", "/edit/[{id}]", array("RssControllersFeeds", "ajax_edit_category"));
                        $r->addRoute("POST", "/edit", array("RssControllersFeeds", "ajax_post_category"));
                    });
                    $r->addGroup("/subscription", function (FastRoute\RouteCollector $r) {
                        $r->addRoute("POST", "/edit", array("RssControllersFeeds", "ajax_post_subscription"));
                        $r->addRoute("GET", "/edit/{sub_id}", array("RssControllersFeeds", "edit_subscription"));
                        $r->addRoute("GET", "/move/{sub_id}/[{cat_id}]", array("RssControllersFeeds", "move_subscription"));
                    });
                });
            });

            $r->addGroup("/links", function(FastRoute\RouteCollector $r) {
                $r->addRoute("GET", "", array("LinksControllersHome", "home"));
                $r->addRoute(array("GET", "POST"), "/edit/[{id}]", array("LinksControllersHome", "edit"));
                $r->addRoute("GET", "/import", array("LinksControllersFile", "import"));
                $r->addRoute("GET", "/info", array("LinksControllersInfo", "info"));
            });

            $r->addGroup("/pro", function(FastRoute\RouteCollector $r) {
                $r->addRoute("GET", "", array("ProControllersKanban", "home"));
                $r->addRoute("GET", "/clients", array("ProControllersClients", "home"));
            });
        }
    }
});

// find a route
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$prefix = isset($_SERVER["DOCUMENT_URI"]) ? str_replace("/index.php", "", $_SERVER["DOCUMENT_URI"]) : null;
$pos = strpos($uri, $prefix);
$uri =  ($pos === 0) ? substr($uri, strlen($prefix)) : $uri;


if (false !== $pos = strpos($uri, '?')) {

    // hack for GET
    $params = substr($uri, $pos+1);
    if (empty($_GET) && $params) {
        foreach(explode("&", $params) as $p) {
            $kv = explode("=", $p);
            $_GET[$kv[0]] = isset($kv[1]) ? $kv[1] : null;
            array_filter($_GET);
        }
    }

    // uri for Route
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
try {
    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
    if ($routeInfo[0] == FastRoute\Dispatcher::FOUND) {
        $params = null;
        ClassesTwig::$route = $uri;
        echo call_user_func_array($routeInfo[1], $routeInfo[2]);
    }
    else {
        throw new Exception("Page '".$uri."' introuvable", 404);
    }
}
catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();

    if (isset($_REQUEST["ajax"]) && $_REQUEST["ajax"]) {
        echo "<div class=\"uk-alert uk-alert-danger\"><b>".$code."</b> ".$message."</div>";
    }
    else {
        $template = ClassesTwig::getInstance();
        echo $template->render("template/views/error.twig", array(
            "code" => $code,
            "message" => $message
        ));
    }
}