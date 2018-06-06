<?php


namespace Bataillon\Controller;

use Bataillon\Mapper\CharactersMapper;
use Bataillon\Models\GuildDataModel;
use Bataillon\Persistance\FileHandler;

class GuildDataController
{
    private const INTERVAL_DAYS = 7;

    /**
     * @var GuildDataModel
     */
    private $guildDataModel;

    /**
     * @var CharactersMapper
     */
    private $charactersMapper;

    /**
     * @var FileHandler
     */
    private $fileHandler;

    public function __construct(GuildDataModel $guildDataModel, CharactersMapper $charactersMapper, FileHandler $fileHandler)
    {
        $this->guildDataModel = $guildDataModel;
        $this->charactersMapper = $charactersMapper;
        $this->fileHandler = $fileHandler;
    }

    public function __invoke()
    {
        $dataPoints = $this->fileHandler->getListOfDataPoints();
        $currentDataPoint = array_shift($dataPoints);

        $comparingDataPoint = $this->findComparingDataPoint($currentDataPoint, $dataPoints);

        unset($dataPoints);
        $currentPlayerData = $this->guildDataModel->getPlayerData($currentDataPoint);
        $comparingPlayerData = $this->guildDataModel->getPlayerData($comparingDataPoint);

        $currentGuildData = $this->guildDataModel->getGuildDataFromPlayerData($currentPlayerData);
        uasort($currentGuildData, function ($a, $b) {
            return $a['power'] < $b['power'];
        });

        $comparingGuildData = $this->guildDataModel->getGuildDataFromPlayerData($comparingPlayerData);

        $comparingPlayerDataFlat = [];
        foreach ($comparingPlayerData as $guild) {
            foreach ($guild as $playerName => $playerData) {
                $comparingPlayerDataFlat[$playerName] = $playerData;
            }
        }

        return [
            'currentGuildData' => $currentGuildData,
            'comparingGuildData' => $comparingGuildData,
            'currentPlayerData' => $currentPlayerData,
            'comparingPlayerData' => $comparingPlayerDataFlat,
        ];
    }

    private function findComparingDataPoint($currentDataPoint, $dataPoints)
    {
        try {
            $currentDataPointDate = new \DateTimeImmutable($currentDataPoint);

            foreach ($dataPoints as $index => $dataPoint) {
                $dataPointDate = new \DateTimeImmutable($dataPoint);

                $dateDiff = $currentDataPointDate->diff($dataPointDate);

                if ($dateDiff->days === static::INTERVAL_DAYS) {
                    return $dataPoint;
                }
            }

            return end($dataPoints);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}