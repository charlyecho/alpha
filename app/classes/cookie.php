<?php

/**
 * Class to manage the cookie
 */
class ClassesCookie
{
    /**
     * Get data from the cookie store
     *
     * @param string    $name       Name of a variable
     * @param mixed     $default    Default value of a variable if not set
     *
     * @return  mixed  Value of a variable
     * @since 1.0
     */
    public static function get($name, $default = null)
    {
        if (self::has($name)) {
            return $_COOKIE[$name];
        }

        return $default;
    }

    /**
     * Set data into the cookie store.
     *
     * @param string    $name       Name of a variable.
     * @param mixed     $value      Value of a variable.
     * @param int       $expire     The time the cookie expires.
     * @param string    $path       The path on the server in which the cookie will be available on.
     * @param string    $domain     The domain that the cookie is available to.
     * @param bool      $secure     Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
     * @param bool      $httponly   When TRUE the cookie will be made accessible only through the HTTP protocol.
     *
     *
     * @return mixed    Old value of a variable.
     * @since 1.0
     */
    public static function set($name, $value = null, $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        $old = self::get($name);

        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);

        return $old;
    }

    /**
     * Check whether data exists in the cookie store
     *
     * @param string    $name Name of variable
     *
     * @return boolean  True if the variable exists
     * @since 1.0
     */
    public static function has($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * @param $name
     * @return bool
     */
    public static function delete($name)
    {
        unset($_COOKIE[$name]);
        return setcookie($name, null, -1);
    }
}