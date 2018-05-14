<?php

$container = require __DIR__ . '/app/bootstrap.php';

$app = new Silly\Edition\PhpDi\Application();

include_once __DIR__ . '/app/config.php';

$app->command('update', Bataillon\Commands\UpdateRosterCommand::class);
$app->command('build', Bataillon\Commands\BuildStaticPagesCommand::class);

$app->setDefaultCommand('build');

try {
    $app->run();
} catch (Exception $e) {
}