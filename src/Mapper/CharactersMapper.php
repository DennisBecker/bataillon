<?php


namespace Bataillon\Mapper;


use Bataillon\Persistance\FileHandler;

class CharactersMapper
{
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var array
     */
    private $characters = [];

    public function __construct(FileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    public function getName($characterId)
    {
        if (empty($this->characters)) {
            $this->initializeCharacters();
        }

        if (array_key_exists($characterId, $this->characters)) {
            return $this->characters[$characterId];
        }

        return "UNKNOWN CHARACTER";
    }

    private function initializeCharacters()
    {
        $rawCharacters = $this->readJsonDataAsArray();

        foreach ($rawCharacters as $character) {
            $this->characters[$character['base_id']] = $character['name'];
        }
    }

    private function readJsonDataAsArray()
    {
        return json_decode($this->fileHandler->read('characters.json'), true);
    }
}