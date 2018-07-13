<?php


namespace Bataillon\Commands;

use Bataillon\Persistance\FileHandler;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand
{
    public function __invoke(InputInterface $input, OutputInterface $output, ContainerInterface $container)
    {
        $fileHandler = $container->get(\Bataillon\Persistance\FileHandler::class);
        $dataPointsList = $fileHandler->getListOfDataPoints();

        try {
            $today = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Berlin'));
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to initialize date");
        }

        foreach ($dataPointsList as $dataPoint) {
            try {
                $dataPointDate = new \DateTimeImmutable($dataPoint, new \DateTimeZone('Europe/Berlin'));
            } catch (\Exception $e) {
                throw new \RuntimeException("Failed to initialize date");
            }
            $dateDiff = $today->diff($dataPointDate);

            if ($dateDiff->days > 7) {
                $fileHandler->clearDirectory(FileHandler::DATA_DIR . 'guilds' . DIRECTORY_SEPARATOR . $dataPoint);
                $fileHandler->removeDirectory(FileHandler::DATA_DIR . 'guilds' . DIRECTORY_SEPARATOR . $dataPoint);
            }
        }
    }
}