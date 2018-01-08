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

        $sql = "SELECT t.*, o.color as organisation_color, o.name as organisation_name, SUM(st.finished) as nb_finished, count(st.id) as nb_subtask
            FROM task t 
            LEFT JOIN subtask st ON st.task_id = t.id
            LEFT JOIN organisation o ON t.organisation_id = o.id 
            WHERE t.user_id = ".$db->quote($user_id)." 
            GROUP BY t.id ";
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
            if (!in_array($t->kanban, array_keys($types))) {
                $t->kanban = "backlog";
            }
            $types[$t->kanban]["list"][] = $t;
        }

        return $types;
    }

    public static function getItem($id, $user_id) {
        $db = ClassesDb::getInstance();
        $sql = "SELECT t.*, o.name as organisation_name, o.color as organisation_color FROM task t LEFT JOIN organisation o ON t.organisation_id = o.id WHERE t.id = ".$db->quote($id)." AND t.user_id = ".$db->quote($user_id);
        $s = $db->prepare($sql);
        $s->execute();
        return $s->fetch();
    }

    public static function getSubTask($id = null) {
        if (!$id) {
            return array();
        }

        $db = ClassesDb::getInstance();
        $sql = "SELECT * FROM subtask WHERE task_id = ".$db->quote($id)." ORDER BY title";
        $s = $db->prepare($sql);
        $s->execute();
        return $s->fetchAll();
    }
}