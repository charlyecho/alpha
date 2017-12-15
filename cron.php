<?php
ini_set('default_charset', 'UTF-8');
require_once __DIR__.'/app/functions.php';
require_once __DIR__.'/vendor/autoload.php';

if (isset($_SERVER["HTTP_HOST"])) {
    die("Cron used in local only");
}

echo ControllersCron::cliUpdate();