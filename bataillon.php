<?php

$container = require __DIR__ . '/app/bootstrap.php';

$app = new Silly\Edition\PhpDi\Application();

include_once __DIR__ . '/app/config.php';

$app->command('update', Bataillon\Commands\RosterCommand::class);
$app->setDefaultCommand('update');

$app->run();

exit(0);
