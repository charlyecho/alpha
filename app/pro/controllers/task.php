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
        $task->subtask = ProHelpersTask::getSubTask($task->id);

        $template = ClassesTwig::getInstance();
        return $template->render("pro/views/task.twig", array(
            "task" => $task
        ));
    }

    public static function editItem() {
        $id = get($_POST, "id");
        $title = get($_POST, "title");
        $comment = get($_POST, "comment");

        $db = ClassesDb::getInstance();
        $sql = "UPDATE task SET title = :title, comment = :comment  WHERE id = ".$db->quote($id);
        $s = $db->prepare($sql);
        $s->execute(array(":title" => $title, ":comment" => $comment));
    }
}