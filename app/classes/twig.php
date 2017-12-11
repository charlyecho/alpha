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
        $loader = new Twig_Loader_Filesystem('app/template');
        $twig = new Twig_Environment($loader, array(
            'cache' => false //'app/cache',
        ));
        $twig->addGlobal('user', HelpersUser::getCurrent());
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