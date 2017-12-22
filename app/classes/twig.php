<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 01/12/17
 * Time: 23:04
 */

class ClassesTwig {
    /**
     * @var null|Twig_Environment
     */
    protected static $template = null;

    public function __construct()
    {
        $path = dirname(__DIR__)."/template";
        $loader = new Twig_Loader_Filesystem($path);
        $twig = new Twig_Environment($loader, array(
            'cache' => false //'app/cache',
        ));
        $twig->addGlobal('user', HelpersUser::getCurrent());
        $twig->addGlobal("messages", ClassesSession::getMessages());
        $twig->addGlobal("ldap", ClassesSession::getInstance()->get("ldap"));
        $twig->addGlobal('prefix', isset($_SERVER["DOCUMENT_URI"]) ? str_replace("/index.php", "", $_SERVER["DOCUMENT_URI"]) : null);
        return self::$template = $twig;
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        $class = get_called_class();

        return new $class();
    }

    public function render($name, array $data = array()) {
        return self::$template->render($name, $data);
    }
}