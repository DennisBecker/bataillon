<?php


namespace Bataillon\Commands;

use Bataillon\Controller\UpdateController;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRosterCommand
{
    public function __invoke(InputInterface $input, OutputInterface $output, ContainerInterface $container)
    {
        $apiCallCount = 2 + count($container->get('GuildList'));
        $progressBar = new ProgressBar($output, $apiCallCount);
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>➤</>");
        $progressBar->setFormat(
            "<fg=white;bg=blue> %status:-45s%</>\n%current%/%max% [%bar%] %percent:3s%%\n🏁  %estimated:-20s%  %memory:20s%\n"
        );

        $container->call(UpdateController::class, [$progressBar, $input->getOption('force')]);
    }
}