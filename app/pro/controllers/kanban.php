<?php
/**
 * Created by PhpStorm.
 * User: charly
 * Date: 07/01/18
 * Time: 14:53
 */

class ProControllersKanban {
    public static function home() {
        $user = HelpersUser::getCurrent();
        $tasks = ProHelpersTask::getList($user->id);
        $kanban = ProHelpersTask::getKanbanTasks($tasks);

        $template = ClassesTwig::getInstance();
        return $template->render("pro/views/kanban.twig", array(
            "kanban" => $kanban
        ));
    }
}