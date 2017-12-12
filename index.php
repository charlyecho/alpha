<?php
/**
 * Hi :)
 */
ob_start();
ini_set('default_charset', 'UTF-8');
date_default_timezone_set("UTC");

require_once __DIR__.'/app/functions.php';
require_once __DIR__.'/vendor/autoload.php';

// routing
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $path_db = __DIR__."/app/db/db.sqlite";

    $r->addRoute("GET", "/", array("ControllersHome", "home"));
    $r->addRoute("GET", "/install", array("ControllersInstall", "home"));

    if (is_file($path_db)) {
        $r->addRoute(array("GET", "POST"), "/login", array("ControllersLogin", "home"));
        $r->addRoute("GET", "/logout", array("ControllersLogin", "logout"));
        $r->addRoute("GET", "/session/{id:\d+}", array("HelpersUser", "session"));//@TODO DELETE THIS !

        $r->addRoute("GET", "/cron/step1", array("HelpersCron", "checkLastModification"));
        $r->addRoute("GET", "/cron/step2", array("HelpersCron", "getData"));
        $r->addRoute("GET", "/cron/step3", array("HelpersCron", "parse"));
        $r->addRoute("GET", "/debug/{id:\d+}", array("ControllersCron", "debug"));
        $r->addRoute("GET", "/update", array("ControllersCron", "update"));

        if (HelpersUser::getCurrent()) {
            $r->addRoute("GET", "/feeds", array("ControllersFeeds", "home"));
            $r->addRoute("POST", "/ajax_flow", array("ControllersFeeds", "ajax"));

            $r->addGroup('/config', function (FastRoute\RouteCollector $r) {
                $r->addRoute('GET', '', array("ControllersFeeds", "config"));
                $r->addRoute('GET', '/export.opml', array("ControllersFeeds", "export"));
                $r->addRoute('POST', '/import', array("ControllersFeeds", "import"));
                $r->addGroup('/category', function (FastRoute\RouteCollector $r) {
                    $r->addRoute("GET", "/edit/[{id}]", array("ControllersFeeds", "ajax_edit_category"));
                    $r->addRoute("POST", "/edit", array("ControllersFeeds", "ajax_post_category"));
                });
                $r->addGroup("/subscription", function (FastRoute\RouteCollector $r) {
                    $r->addRoute("POST", "/edit", array("ControllersFeeds", "ajax_post_subscription"));
                    $r->addRoute("GET", "/edit/{sub_id}", array("ControllersFeeds", "edit_subscription"));
                    $r->addRoute("GET", "/move/{sub_id}/[{cat_id}]", array("ControllersFeeds", "move_subscription"));
                });
            });
        }
    }
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

try {
    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
    if ($routeInfo[0] == FastRoute\Dispatcher::FOUND) {
        $params = null;
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
        echo $template->render("views/error.twig", array(
            "code" => $code,
            "message" => $message
        ));
    }
}