<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 02/12/17
 * Time: 11:22
 */

class ClassesDb extends PDO {
    /**
     * @var null|PDO
     */
    protected static $instances = array();

    public function __construct($name = 'default', array $config = array())
    {
        $c = get_called_class();

        parent::__construct("sqlite:app/db/db.sqlite");
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        self::$instances[$c][$name] = $this;
    }

    /**
     * @param string $name
     * @param array $config
     * @return PDO
     */
    public static function getInstance($name = 'default', array $config = array()) {
        $c = get_called_class();

        if (!isset(self::$instances[$c][$name])) {
            new $c($name, $config);
        }

        return self::$instances[$c][$name];
    }
}