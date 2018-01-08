<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 07/01/18
 * Time: 14:38
 */

class ProControllersClients {
    public static function home() {

        $user = HelpersUser::getCurrent();


        if (!empty($_POST)) {
            $name = get($_POST, "name");

            $db = ClassesDb::getInstance();
            $sql = "INSERT INTO organisation (user_id, name) VALUES (:user_id, :name) ; ";
            $s = $db->prepare($sql);
            $s->execute(array(":user_id" => $user->id, ":name" => $name));
        }

        $list = ProHelpersClients::getList($user->id);

        $template = ClassesTwig::getInstance();
        return $template->render("pro/views/clients.twig", array(
            "list" => $list
        ));
    }

    public static function update() {
        $id = get($_POST, "id");
        $color = get($_POST, "color");
        $text = get($_POST, "text");

        $db = ClassesDb::getInstance();
        if ($text) {
            $sql = "UPDATE organisation SET name = :title WHERE id = :id ";
            $array = array(":id" => $id, ":title" => $text);
        }
        else {
            $sql = "UPDATE organisation SET color = :color WHERE id = :id ";
            $array = array(":id" => $id, ":color" => $color);
        }
        $q = $db->prepare($sql);
        $q->execute($array);
    }
}