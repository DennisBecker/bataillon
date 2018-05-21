<?php

namespace Bataillon\Controller;

use Bataillon\Clients\SWGoH;
use Bataillon\Persistance\FileHandler;
use Symfony\Component\Console\Helper\ProgressBar;

class UpdateController
{
    const CHARACTERS_FILENAME = 'characters.json';
    const SHIPS_FILENAME = 'ships.json';

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

    public function __invoke(ProgressBar $progressBar, $forceUpdate)
    {
        $progressBar->start();

        $this->updateCharacters($progressBar);
        $this->updateShips($progressBar);
        $this->updateGuilds($progressBar, $forceUpdate);

        $progressBar->finish();
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

    private function updateGuilds(ProgressBar $progressBar, $forceUpdate)
    {
        try {
            $timeZone = new \DateTimeZone('Europe/Berlin');
            $today = new \DateTimeImmutable('now', $timeZone);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }

        if (!$forceUpdate && (int)$today->format('w') !== 1) {
            return;
        }

        $currentGuildDir = 'guilds/' . $today->format('Y-m-d') . '/';
        $this->fileHandler->createDirectory($currentGuildDir);

        foreach ($this->guildList as $guild => $uri) {
            $progressBar->setMessage("Updating Guild " . $guild, 'status');

            $guildFilename = $currentGuildDir . $guild . '.json';
            $this->fileHandler->write($guildFilename, $this->client->getGuildData($uri));

            $progressBar->advance();
        }
    }
}