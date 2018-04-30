<?php


namespace Bataillon\Controller;


use Bataillon\Entity\Character;
use Bataillon\Mapper\CharactersMapper;
use Bataillon\Persistance\FileHandler;

class GuildController
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
            $guildData[$guild]['member'] = $this->buildMemberData($this->readJsonDataAsArray($guild), $guild);
            $guildData[$guild]['power'] = array_sum(array_column($guildData[$guild]['member'], 'power'));
        }

        return $guildData;
    }

    private function buildMemberData($guildData, $guild)
    {
        $playerCharacters = [];
        foreach ($guildData as $characterId => $memberCharacterData) {
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
                        throw new \InvalidArgumentException(sprintf('Combat type %d is UNKNOWN.', $character['combat_type']));
                }
            }
        }

        return $playerCharacters;
    }

    private function readJsonDataAsArray($guild)
    {
        return json_decode($this->fileHandler->read('guilds/' . $guild . '.json'), true);
    }
}