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
            $guildData[$guild]['power_old'] = array_sum(array_column($guildData[$guild]['member'], 'power_old'));
            $guildData[$guild]['power_characters'] = array_sum(array_column($guildData[$guild]['member'], 'power_characters'));
            $guildData[$guild]['power_characters_old'] = array_sum(array_column($guildData[$guild]['member'], 'power_characters_old'));
            $guildData[$guild]['power_ships'] = array_sum(array_column($guildData[$guild]['member'], 'power_ships'));
            $guildData[$guild]['power_ships_old'] = array_sum(array_column($guildData[$guild]['member'], 'power_ships_old'));
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
                        'power_old' => 0,
                        'power_characters' => 0,
                        'power_characters_old' => 0,
                        'power_ships' => 0,
                        'power_ships_old' => 0,
                        'guild' => $guild,
                        'characters' => [],
                        'ships' => [],
                    ];
                }

                switch ($character['combat_type']) {
                    // Characters
                    case 1:
                        $playerCharacters[$character['player']]['characters'][$characterId] = [
                            'power' => $character['power'],
                            'rarity' => $character['rarity'],
                            'level' => $character['level'],
                            'gear' => $character['gear_level'],
                        ];
                        ksort($playerCharacters[$character['player']]['characters']);

                        $playerCharacters[$character['player']]['power'] += $character['power'];
                        $playerCharacters[$character['player']]['power_characters'] += $character['power'];
                        break;
                    // Ships
                    case 2:
                        $playerCharacters[$character['player']]['ships'][$characterId] = [
                            'power' => $character['power'],
                            'rarity' => $character['rarity'],
                            'level' => $character['level'],
                        ];
                        ksort($playerCharacters[$character['player']]['ships']);

                        $playerCharacters[$character['player']]['power'] += $character['power'];
                        $playerCharacters[$character['player']]['power_ships'] += $character['power'];
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
                        || (!array_key_exists($characterId, $playerCharacters[$character['player']]['characters']))
                        && !array_key_exists($characterId, $playerCharacters[$character['player']]['ships'])) {
                        continue;
                    }

                    switch ($character['combat_type']) {
                        case 1:
                            $playerCharacters[$character['player']]['characters'][$characterId] += [
                                'power_old' => $character['power'],
                                'rarity_old' => $character['rarity'],
                                'level_old' => $character['level'],
                                'gear_old' => $character['gear_level'],
                            ];

                            $playerCharacters[$character['player']]['power_old'] += $character['power'];
                            $playerCharacters[$character['player']]['power_characters_old'] += $character['power'];
                            break;
                        case 2:
                            $playerCharacters[$character['player']]['ships'][$characterId] += [
                                'power_old' => $character['power'],
                                'rarity_old' => $character['rarity'],
                                'level_old' => $character['level'],
                            ];

                            $playerCharacters[$character['player']]['power_old'] += $character['power'];
                            $playerCharacters[$character['player']]['power_ships_old'] += $character['power'];
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