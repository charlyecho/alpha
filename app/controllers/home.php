<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 01/12/17
 * Time: 21:32
 */

class ControllersHome {
    public static function home() {
        if ($user = HelpersUser::getCurrent()) {
            return ControllersFeeds::home();
        }

        redirect("/login");
    }
}