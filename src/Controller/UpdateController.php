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

        $lastModifiedDate = $this->fileHandler->getLastModifiedDate(static::CHARACTERS_FILENAME);

        if ($lastModifiedDate < $this->getTimeStampOfTodaysMidnight()) {
            $this->fileHandler->write(static::CHARACTERS_FILENAME, $this->client->getCharacters());
        }

        $progressBar->advance();
    }

    private function updateShips(ProgressBar $progressBar)
    {
        $progressBar->setMessage("Updating Ships", 'status');

        $lastModifiedDate = $this->fileHandler->getLastModifiedDate(static::SHIPS_FILENAME);

        if ($lastModifiedDate < $this->getTimeStampOfTodaysMidnight()) {
            $this->fileHandler->write(static::SHIPS_FILENAME, $this->client->getShips());
        }

        $progressBar->advance();
    }

    private function updateGuilds(ProgressBar $progressBar)
    {
        foreach ($this->guildList as $guild => $uri) {
            $progressBar->setMessage("Updating Guild " . $guild, 'status');

            $guildFilename = 'guilds/' . $guild . '.json';
            $lastModifiedDate = $this->fileHandler->getLastModifiedDate($guildFilename);

            if ($lastModifiedDate < $this->getTimeStampOfTodaysMidnight()) {
                $this->fileHandler->write($guildFilename, $this->client->getGuildData($uri));
            }

            $progressBar->advance();
        }
    }

    private function getTimeStampOfTodaysMidnight()
    {
        $date = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Berlin'));

        return $date->setTime(0, 0, 0,0)->getTimestamp();
    }
}