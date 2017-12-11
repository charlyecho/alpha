<?php

/**
 * Class to manage the session
 */
class ClassesSession
{
    static $config = array(
        "lifetime" => 31536000,
        "path" => "/",
        "domain" => ".domain.tld",
        "secure" => false,
        "secret" => "bJfYz2yi2byRbNEHWPXVFNapaze48&etspj1f34?bp)CsFwhUndoJA"
    );


    private static $instance;

    /**
     * Returns a reference to the global Session object, only creating it
     * if it doesn't already exist.
     *
     * @return self
     * @since 1.5
     */
    public static function getInstance()
    {
        $c = get_called_class();

        if (!isset(self::$instance[$c])) {
            self::$instance[$c] = new $c();
        }

        return self::$instance[$c];
    }

    /**
     * Constructor
     *
     * @since 1.5
     */
    protected function __construct()
    {
        // Get the classname to create a singleton
        $c = get_called_class();

        // Set session cookie
        session_set_cookie_params(self::$config["lifetime"], self::$config["path"], self::$config["domain"], self::$config["secure"], true);
        // set the current session name
        session_name($this->getCookieName('session'));
        // set the current session id
        session_id();

        // start session
        session_start();

        self::$instance[$c] = $this;
    }

    /**
     * Get session name.
     *
     * @return string   The session name.
     * @since 1.5
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Get session id.
     *
     * @return string   The session id.
     * @since 1.5
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Get name of the cookie.
     *
     * @param string $name Name of a variable.
     *
     * @return string
     * @since 1.5
     */
    protected function getCookieName($value)
    {

        $agent = '';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = $_SERVER['HTTP_USER_AGENT'];
        }

        return md5(self::$config["secret"].$value.$agent);
    }

    /**
     * Get data from the session store.
     *
     * @param string    $name       Name of a variable.
     * @param mixed     $default    Default value of a variable if not set.
     * @param string    $namespace  Namespace to use, default to 'default'.
     *
     * @return mixed    Value of a variable.
     * @since 1.5
     */
    public function get($name, $default = null, $namespace = 'Rs')
    {
        if ($this->has($name, $namespace)) {
            return $_SESSION['__'.$namespace][$name];
        }

        return $default;
    }

    /**
     * Set data into the session store.
     *
     * @param string    $name       Name of a variable.
     * @param mixed     $value      Value of a variable.
     * @param string    $namespace  Namespace to use, default to 'default'.
     *
     * @return self    The instance of the self class.
     * @since 1.5
     */
    public function set($name, $value = null, $namespace = 'Rs')
    {
        $_SESSION['__'.$namespace][$name] = $value;

        return $this;
    }

    /**
     * Check whether data exists in the session store.
     *
     * @param string    $name       Name of variable.
     * @param string    $namespace  Namespace to use, default to 'default'.
     *
     * @return boolean  True if the variable exists.
     * @since 1.5
     */
    public function has($name, $namespace = 'Rs')
    {
        return isset($_SESSION['__'.$namespace][$name]);
    }

    /**
     * Unset data from the session store.
     *
     * @param string    $name       Name of variable.
     * @param string    $namespace  Namespace to use, default to 'default'.
     *
     * @return mixed    The value from session or NULL if not set.
     * @since 1.5
     * @deprecated deprecated since v1.5 : See delete()
     */
    public function clear($name, $namespace = 'Rs')
    {
        return $this->delete($name, $namespace);
    }

    /**
     * Unset data from the session store.
     *
     * @param string    $name       Name of variable.
     * @param string    $namespace  Namespace to use, default to 'default'.
     *
     * @return self   The instance of the self class.
     * @since 1.5
     */
    public function delete($name, $namespace = 'Rs')
    {
        unset($_SESSION['__'.$namespace][$name]);

        return $this;
    }

    /**
     * Frees all session variables and destroys all data registered to a session.
     *
     * This method resets the $_SESSION variable and destroys all of the data associated
     * with the current session in its storage (file or DB). It forces new session to be
     * started after this method is called.
     *
     * @return void
     * @since 1.5
     */
    public function destroy()
    {
        // In order to kill the session altogether, such as to log the user out, the session id
        // must also be unset. If a cookie is used to propagate the session id (default behavior),
        // then the session cookie must be deleted.
        ClassesCookie::delete($this->getName());

        session_regenerate_id(true);
        session_unset();
        session_destroy();

        unset(self::$instance[get_called_class()]);
    }
}