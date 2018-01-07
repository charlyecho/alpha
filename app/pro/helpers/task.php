<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 07/01/18
 * Time: 19:23
 */

class ProHelpersTask {
    public static function getList($user_id) {
        $db = ClassesDb::getInstance();
        $sql = "SELECT t.*, o.color as organisation_color, o.name as organisation_name FROM task t LEFT JOIN organisation o ON t.organisation_id = o.id WHERE t.user_id = ".$db->quote($user_id)." ORDER BY t.priority DESC";
        $q = $db->prepare($sql);
        $q->execute();
        $tasks = $q->fetchAll();
        return $tasks;
    }

    public static function getKanbanTasks($list) {
        $types = array(
            "backlog" => array(
                "icon" => "list",
                "list" => array()
            ),
            "coding" => array(
                "icon" => "code",
                "list" => array()
            ),
            "testing" => array(
                "icon" => "test-tube",
                "list" => array()
            ),
            "approval" => array(
                "icon" => "ui-zoom-in",
                "list" => array()
            ),
            "done" => array(
                "icon" => "check",
                "list" => array()
            )
        );
        foreach($list as $t) {
            $types[$t->kanban]["list"][] = $t;
        }

        return $types;
    }
}