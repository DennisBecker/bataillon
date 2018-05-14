<?php


namespace Bataillon\Controller;

use Bataillon\Mapper\CharactersMapper;
use Bataillon\Persistance\FileHandler;

class GuildDataController
{
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var array
     */
    private $guildList;

    /**
     * @var CharactersMapper
     */
    private $charactersMapper;

    public function __construct(FileHandler $fileHandler, CharactersMapper $charactersMapper, array $guildList)
    {
        $this->fileHandler = $fileHandler;
        $this->guildList = $guildList;
        $this->charactersMapper = $charactersMapper;
    }

    public function __invoke()
    {
        $guildData = [];
        foreach (array_keys($this->guildList) as $guild) {
            $guildData[$guild]['member'] = $this->buildMemberData($guild);
            $guildData[$guild]['power'] = array_sum(array_column($guildData[$guild]['member'], 'power'));
        }

        uasort($guildData, function ($a, $b) {
            return $a['power'] < $b['power'];
        });

        return $guildData;
    }

    private function buildMemberData($guild)
    {
        $guildData = $this->fileHandler->readGuildDataOfLastTwoDataPoints($guild);

        $playerCharacters = [];
        foreach (array_shift($guildData) as $characterId => $memberCharacterData) {
            foreach ($memberCharacterData as $character) {

                if (!isset($character['url'])) {
                    $character['url'] = '';
                }

                if (!isset($playerCharacters[$character['player']])) {
                    $playerCharacters[$character['player']] = [
                        'name' => $character['player'],
                        'collection_url' => $character['url'],
                        'power' => 0,
                        'guild' => $guild,
                        'characters' => [],
                        'ships' => [],
                    ];
                }

                switch ($character['combat_type']) {
                    case 1:
                        $playerCharacters[$character['player']]['characters'][$characterId] = [
                            'power' => $character['power'],
                            'rarity' => $character['rarity'],
                            'level' => $character['level'],
                            'gear' => $character['gear_level'],
                        ];
                        ksort($playerCharacters[$character['player']]['characters']);

                        $playerCharacters[$character['player']]['power'] += $character['power'];
                        break;
                    case 2:
                        $playerCharacters[$character['player']]['ships'][$characterId] = [
                            'power' => $character['power'],
                            'rarity' => $character['rarity'],
                            'level' => $character['level'],
                        ];
                        ksort($playerCharacters[$character['player']]['ships']);

                        $playerCharacters[$character['player']]['power'] += $character['power'];
                        break;
                    default:
                        throw new \InvalidArgumentException(sprintf('Combat type %d is UNKNOWN.',
                            $character['combat_type']));
                }
            }
        }

        if (!empty($guildData)) {
            foreach (array_shift($guildData) as $characterId => $memberCharacterData) {
                foreach ($memberCharacterData as $character) {
                    if (!array_key_exists($character['player'], $playerCharacters)
                        || !array_key_exists($characterId, $playerCharacters[$character['player']]['characters'])) {
                        break;
                    }

                    switch ($character['combat_type']) {
                        case 1:
                            $playerCharacters[$character['player']]['characters'][$characterId] += [
                                'power_old' => $character['power'],
                                'rarity_old' => $character['rarity'],
                                'level_old' => $character['level'],
                                'gear_old' => $character['gear_level'],
                            ];
                            break;
                        case 2:
                            $playerCharacters[$character['player']]['ships'][$characterId] += [
                                'power_old' => $character['power'],
                                'rarity_old' => $character['rarity'],
                                'level_old' => $character['level'],
                            ];
                            break;
                        default:
                            throw new \InvalidArgumentException(sprintf('Combat type %d is UNKNOWN.',
                                $character['combat_type']));
                    }
                }
            }
        }

        uasort($playerCharacters, function ($a, $b) {
            return $a['power'] < $b['power'];
        });

        return $playerCharacters;
    }
}