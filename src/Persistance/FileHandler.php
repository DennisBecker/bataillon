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

    public function getLastModifiedDate($filename) : int
    {
        $fileInfo = new \SplFileInfo(static::DATA_DIR . $filename);

        if ($fileInfo->isFile()) {
            return $fileInfo->getMTime();
        }

        return 0;
    }
}