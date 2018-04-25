<?php


namespace Bataillon\Commands;

use Bataillon\Controller\GuildController;
use Bataillon\Controller\UpdateController;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class RosterCommand
{
    public function __invoke(OutputInterface $output, ContainerInterface $container)
    {
        $progressBar = new ProgressBar($output, 12);
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>➤</>");
        $progressBar->setFormat(
            "<fg=white;bg=blue> %status:-45s%</>\n%current%/%max% [%bar%] %percent:3s%%\n🏁  %estimated:-20s%  %memory:20s%\n"
        );

        $container->call(UpdateController::class, [$progressBar]);
        $container->call(GuildController::class);
    }
}