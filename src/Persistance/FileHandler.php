<?php


namespace Bataillon\Persistance;


class FileHandler
{
    const DATA_DIR = __DIR__ . '/../../data/';

    public function read($filename)
    {
        return file_get_contents(static::DATA_DIR . $filename);
    }

    public function write($filename, $data)
    {
        file_put_contents(static::DATA_DIR . $filename, $data);
    }

    public function readGuildDataOfLastTwoDataPoints($guild)
    {
        $directories = new \DirectoryIterator(static::DATA_DIR . 'guilds');

        $dataPoints = [];
        foreach ($directories as $directory) {
            if (!$directory->isDir() || $directory->isDot()) {
                continue;
            }

            $dataPoints[$directory->getFilename()] = json_decode($this->read('guilds/' . $directory->getFilename() . '/' . $guild . '.json'), true);
        }

        return array_reverse($dataPoints, true);
    }

    public function getLastModifiedDate($filename) : int
    {
        $fileInfo = new \SplFileInfo(static::DATA_DIR . $filename);

        if ($fileInfo->isFile()) {
            return $fileInfo->getMTime();
        }

        return 0;
    }

    public function createDirectory($path)
    {
        if (!file_exists(static::DATA_DIR . $path)) {
            mkdir(static::DATA_DIR . $path, 0777, true);
        }
    }
}