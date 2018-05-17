<?php

$container = require __DIR__ . '/app/bootstrap.php';

$app = new Silly\Edition\PhpDi\Application();

include_once __DIR__ . '/app/config.php';

$app->command('update [--force]', Bataillon\Commands\UpdateRosterCommand::class)
    ->descriptions('Update characters, ships and guild data', [
        '--force' => 'Run update without last update check',
    ])
;
$app->command('build', Bataillon\Commands\BuildStaticPagesCommand::class)
    ->descriptions('Build static pages');

try {
    $app->run();
} catch (Exception $e) {
}