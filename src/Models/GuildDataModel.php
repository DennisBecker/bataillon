<?php


namespace Bataillon\Models;


use Bataillon\Persistance\FileHandler;

class GuildDataModel
{
    const GUILD_DATA_DIR = __DIR__ . '/../../data/guilds/';
    /**
     * @var FileHandler
     */
    private $fileHandler;

    public function __construct(FileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    public function getPlayerData($dataPoint)
    {
        $guildData = [];
        $filesystemIterator = new \FilesystemIterator(static::GUILD_DATA_DIR . $dataPoint, \FilesystemIterator::SKIP_DOTS);
        foreach ($filesystemIterator as $file) {
            $guildName = $file->getBasename('.json');
            $data = json_decode($this->fileHandler->read('guilds/' . $dataPoint . '/' . $file->getFilename()), true);

            $guildData[$guildName] = $this->buildMemberData($data, $guildName);
        }

        return $guildData;
    }

    public function getGuildDataFromPlayerData($playerData)
    {
        $guildData = [];
        foreach ($playerData as $guild => $member) {
            $guildData[$guild]['power'] = array_sum(array_column($member, 'power'));
            $guildData[$guild]['power_characters'] = array_sum(array_column($member, 'power_characters'));
            $guildData[$guild]['power_ships'] = array_sum(array_column($member, 'power_ships'));
            $guildData[$guild]['member'] = count($member);
        }

        return $guildData;
    }

    private function buildMemberData($memberData, $guildName)
    {
        $playerCharacters = [];
        foreach ($memberData as $characterId => $memberCharacterData) {
            foreach ($memberCharacterData as $character) {

                if (!isset($character['url'])) {
                    $character['url'] = '';
                }

                if (!isset($playerCharacters[$character['player']])) {
                    $playerCharacters[$character['player']] = [
                        'name' => $character['player'],
                        'collection_url' => $character['url'],
                        'power' => 0,
                        'power_characters' => 0,
                        'power_ships' => 0,
                        'guild' => $guildName,
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

        uasort($playerCharacters, function ($a, $b) {
            return $a['power'] < $b['power'];
        });

        return $playerCharacters;
    }
}