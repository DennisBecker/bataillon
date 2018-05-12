<?php


namespace Bataillon\Commands;

use Bataillon\Controller\GuildController;
use Bataillon\Controller\UpdateController;
use Bataillon\Persistance\FileHandler;
use PhpParser\Node\Scalar\MagicConst\File;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class RosterCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    private $distPath = __DIR__ . '/../../dist/';

    private $webSourcePath = __DIR__ . '/../../app/src/';

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(OutputInterface $output, ContainerInterface $container)
    {
        $this->output = $output;

        $apiCallCount = 2 + count($container->get('GuildList'));
        $progressBar = new ProgressBar($output, $apiCallCount);
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>➤</>");
        $progressBar->setFormat(
            "<fg=white;bg=blue> %status:-45s%</>\n%current%/%max% [%bar%] %percent:3s%%\n🏁  %estimated:-20s%  %memory:20s%\n"
        );

        $container->call(UpdateController::class, [$progressBar]);
        $guildData = $container->call(GuildController::class);

        $this->cleanOutputDirectory();
        $this->copyWebSourceFiles();

        $fileHandler = new FileHandler();
        $raids = json_decode($fileHandler->read('raids.json'), true);
        $characters = [];
         foreach (json_decode($fileHandler->read('characters.json'), true) as $char) {
             $characters[$char['base_id']] = $char;
         }

        $this->render('index.html.twig', 'index.html', ['guilds' => $guildData, 'activeGuild' => '']);

        foreach ($guildData as $guild => $data) {
            $this->render('guildOverview.html.twig', $guild . '/index.html', [
                'guilds' => $guildData,
                'activeGuild' => $guild,
                'guildName' => $guild,
                'guild' => $data,
            ]);

            foreach ($data['member'] as $memberName => $memberData) {
                $this->render('memberOverview.html.twig', $guild . '/' . $memberName .'/index.html', [
                    'guilds' => $guildData,
                    'activeGuild' => $guild,
                    'name' => $memberName,
                    'characters' => $memberData['characters'],
                    'raids' => $raids,
                ]);

                foreach ($raids as $raid => $teams) {
                    $this->render('raids/' . $raid . '.html.twig', $guild . '/' . $memberName . '/'. $raid .'.html', [
                        'guilds' => $guildData,
                        'activeGuild' => $guild,
                        'name' => $memberName,
                        'characters' => $characters,
                        'playerCharacters' => $memberData['characters'],
                        'raid' => $raid,
                        'raidTeams' => $teams,
                    ]);
                }
            }
        }
    }

    public function render($template, $outfile, $data)
    {
        try {
            $fileInfo = new \SplFileInfo(($this->distPath . $outfile));
            if (!file_exists($fileInfo->getPath())) {
                mkdir($fileInfo->getPath(), 0777, true);
            }

            file_put_contents($this->distPath . $outfile, $this->twig->render($template, $data));
        } catch (\Exception $e) {
            $this->output->write($e->getMessage());
            throw new \RuntimeException($e);
        }
    }

    protected function cleanOutputDirectory()
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->distPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        /**
         * @var string $filename
         * @var \SplFileInfo $fileInfo
         */
        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
            } else {
                unlink($filename);
            }
        }
    }

    protected function copyWebSourceFiles()
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->webSourcePath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        /**
         * @var string $filename
         * @var \SplFileInfo $fileInfo
         */
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($this->distPath . $iterator->getSubPathName(), 0777, true);
            } else {
                copy($item, $this->distPath . $iterator->getSubPathName());
            }
        }
    }
}