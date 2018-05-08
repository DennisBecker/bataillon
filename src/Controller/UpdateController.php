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

    public function __invoke(ProgressBar $progressBar)
    {
        $progressBar->start();

        $this->updateCharacters($progressBar);
        $this->updateShips($progressBar);
        $this->updateGuilds($progressBar);

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

    private function updateGuilds(ProgressBar $progressBar)
    {
        $updates = json_decode($this->fileHandler->read('updates.json'), true);
        try {
            $timeZone = new \DateTimeZone('Europe/Berlin');
            $lastUpdate = new \DateTimeImmutable($updates['lastUpdated'], $timeZone);
            $today = new \DateTimeImmutable('now', $timeZone);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }

        if ($today->diff($lastUpdate)->days === 0) {
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

        $updates['lastUpdated'] = $today->format('Y-m-d');
        $this->fileHandler->write('updates.json', json_encode($updates));
    }

    private function getTimeStampOfTodaysMidnight()
    {
        try {
            $date = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Berlin'));
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }

        return $date->setTime(0, 0, 0,0)->getTimestamp();
    }
}