<?php

namespace Bataillon\Controller;

use Bataillon\Clients\SWGoH;
use Bataillon\Persistance\FileHandler;
use Symfony\Component\Console\Helper\ProgressBar;

class UpdateController
{
    const CHARACTERS_FILENAME = 'characters.json';
    const SHIPS_FILENAME = 'ships.json';
    const DATA_DIR = __DIR__ . '/../../data/';

    /**
     * @var SWGoH
     */
    private $client;

    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var array
     */
    private $guildList;

    public function __construct(SWGoH $client, FileHandler $fileHandler, array $guildList)
    {
        $this->client = $client;
        $this->fileHandler = $fileHandler;
        $this->guildList = $guildList;
    }

    /**
     * @param ProgressBar $progressBar
     * @param bool $removeOutdated
     * @param bool $forceUpdate
     */
    public function __invoke(ProgressBar $progressBar, $removeOutdated, $forceUpdate)
    {
        $progressBar->start();

        $this->updateCharacters($progressBar);
        $this->updateShips($progressBar);

        $this->updateGuilds($progressBar);

        $progressBar->finish();

        $this->removeOutdatedGuildData($removeOutdated);
    }

    private function updateCharacters(ProgressBar $progressBar)
    {
        $progressBar->setMessage("Updating CharactersModel", 'status');

        $this->fileHandler->write(static::CHARACTERS_FILENAME, $this->client->getCharacters());

        $progressBar->advance();
    }

    private function updateShips(ProgressBar $progressBar)
    {
        $progressBar->setMessage("Updating Ships", 'status');

        $this->fileHandler->write(static::SHIPS_FILENAME, $this->client->getShips());

        $progressBar->advance();
    }

    private function updateGuilds(ProgressBar $progressBar)
    {
        $currentGuildDir = 'guilds/' . $this->getCurrentDateTime()->format('Y-m-d') . '/';
        $this->fileHandler->createDirectory($currentGuildDir);

        foreach ($this->guildList as $guild => $uri) {
            $progressBar->setMessage("Updating Guild " . $guild, 'status');

            $guildFilename = $currentGuildDir . $guild . '.json';
            $this->fileHandler->write($guildFilename, $this->client->getGuildData($uri));

            $progressBar->advance();
        }
    }

    /**
     * @param bool $removeOutdated
     */
    private function removeOutdatedGuildData($removeOutdated)
    {
        if (!$removeOutdated) {
            return;
        }

        $filesystemIterator = new \FilesystemIterator(static::DATA_DIR . 'guilds', \FilesystemIterator::SKIP_DOTS);

        $guildDataDirectories = [];
        foreach ($filesystemIterator as $directory) {
            $guildDataDirectories[] = $directory->getFilename();
        }

        usort($guildDataDirectories, function($a, $b) {
            $dateA = new \DateTimeImmutable($a);
            $dateB = new \DateTimeImmutable($b);

            if ($dateA < $dateB) {
                return 1;
            }

            if ($dateA > $dateB) {
                return -1;
            }

            return 0;
        });

        // TODO find a way to remove old data
        /*
        foreach (array_slice($guildDataDirectories, 2) as $deletableDir) {
            $this->fileHandler->clearDirectory(static::DATA_DIR . 'guilds/' . $deletableDir);
            $this->fileHandler->removeDirectory(static::DATA_DIR . 'guilds/' . $deletableDir);
        }*/
    }

    /**
     * @return \DateTimeImmutable
     */
    protected function getCurrentDateTime(): \DateTimeImmutable
    {
        try {
            $timeZone = new \DateTimeZone('Europe/Berlin');
            $today = new \DateTimeImmutable('now', $timeZone);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
        return $today;
    }
}