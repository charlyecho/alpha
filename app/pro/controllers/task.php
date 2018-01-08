<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 08/01/18
 * Time: 00:26
 */

class ProControllersTask {
    public static function view($id = null) {
        $user = HelpersUser::getCurrent();

        $task = ProHelpersTask::getItem($id, $user->id);
        if ($task) {
            $task->nb_finished = 0;
            $task->subtask = ProHelpersTask::getSubTask($task->id);
            foreach ($task->subtask as $s) {
                $task->nb_finished += $s->finished;
            }
        }

        $template = ClassesTwig::getInstance();
        return $template->render("pro/views/task.twig", array(
            "task" => $task,
            "id" => $id
        ));
    }

    public static function editItem() {
        $id = get($_POST, "id");
        $title = get($_POST, "title");
        $comment = get($_POST, "comment");
        $del = get($_POST, "del");

        $db = ClassesDb::getInstance();
        if ($del) {
            $sql = "DELETE FROM subtask WHERE task_id = " . $db->quote($id);
            $s = $db->prepare($sql);
            $s->execute();

            $sql = "DELETE FROM task WHERE id = " . $db->quote($id);
            $s = $db->prepare($sql);
            $s->execute();
        }
        else {
            $sql = "UPDATE task SET title = :title, comment = :comment  WHERE id = " . $db->quote($id);
            $s = $db->prepare($sql);
            $s->execute(array(":title" => $title, ":comment" => $comment));
        }
    }

    public static function editSub() {
        $id = get($_POST, "id");
        $val = get($_POST, "val");

        $db = ClassesDb::getInstance();
        $sql = "UPDATE subtask SET finished = :val  WHERE id = :id";
        $s = $db->prepare($sql);
        $s->execute(array(":val" => $val, ":id" => $id));
    }

    public static function addSub() {
        $id = get($_POST, "id");
        $name = get($_POST, "name");

        $db = ClassesDb::getInstance();
        $sql = "INSERT INTO subtask (task_id, title, finished) VALUES (:task_id, :name, 0)";
        $s = $db->prepare($sql);
        $s->execute(array(":task_id" => $id, ":name" => $name));
    }
}