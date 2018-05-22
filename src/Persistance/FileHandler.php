<?php


namespace Bataillon\Persistance;

class FileHandler
{
    const DATA_DIR = __DIR__ . '/../../data/';

    public function write($filename, $data)
    {
        file_put_contents(static::DATA_DIR . $filename, $data);
    }

    public function readGuildDataOfLastTwoDataPoints($guild)
    {
        $filesystemIterator = new \FilesystemIterator(static::DATA_DIR . 'guilds', \FilesystemIterator::SKIP_DOTS);

        $dataPoints = [];
        foreach ($filesystemIterator as $directory) {
            try {
                $dataPoints[$directory->getFilename()] = json_decode($this->read('guilds/' . $directory->getFilename() . '/' . $guild . '.json'),
                    true);
            } catch (FileNotFoundException $e) {

            }
        }

        uksort($dataPoints, function($a, $b) {
            $dateA = new \DateTimeImmutable($a);
            $dateB = new \DateTimeImmutable($b);

            if ($dateA < $dateB) {
                return 1;
            }

            if ($dateA > $dateB) {
                return -1;
            }

            return 0;
        });

        return array_slice($dataPoints, 0, 2);
    }

    public function read($filename)
    {
        if (!file_exists(static::DATA_DIR . $filename)) {
            throw new FileNotFoundException("File not found: " . static::DATA_DIR . $filename);
        }

        return file_get_contents(static::DATA_DIR . $filename);
    }

    public function createDirectory($path)
    {
        if (!file_exists(static::DATA_DIR . $path)) {
            mkdir(static::DATA_DIR . $path, 0777, true);
        }
    }

    public function clearDirectory($directoryPath)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directoryPath,
            \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        /**
         * @var string $filename
         * @var \SplFileInfo $fileInfo
         */
        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
            } else {
                if ($fileInfo->getFilename() === '.gitkeep') {
                    continue;
                }

                unlink($filename);
            }
        }
    }

    public function removeDirectory($directoryPath)
    {
        rmdir($directoryPath);
    }
}