<?php


namespace Bataillon\Commands;

use Bataillon\Controller\UpdateController;
use Bataillon\Mapper\CharactersMapper;
use Bataillon\Persistance\FileHandler;
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

        $charactersMapper = $container->get(CharactersMapper::class);
        $fileHandler = $container->get(FileHandler::class);

        $guildsDir = new \DirectoryIterator(__DIR__ . '/../../data/guilds');

        foreach ($guildsDir as $guildFile) {
            if (!$guildFile->isFile()) {
                continue;
            }

            var_dump($guildFile->getBasename());
        }

        $output->writeln($charactersMapper->getName("AAYLASECURA"));
    }
}