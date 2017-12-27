<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 27/12/17
 * Time: 00:38
 */

class ClassesRoutecollection extends FastRoute\RouteCollector {

    /**
     * @param string|string[] $httpMethod
     * @param string $route
     * @param callable $handler
     */
    public function addRoute($httpMethod, $route, $handler)
    {
        parent::addRoute($httpMethod, $route, $handler);
    }
}