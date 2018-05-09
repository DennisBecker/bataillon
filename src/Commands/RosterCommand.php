<?php


namespace Bataillon\Commands;

use Bataillon\Controller\GuildController;
use Bataillon\Controller\UpdateController;
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

        $this->render('index.html.twig', 'index.html', ['guilds' => $guildData]);

        foreach ($guildData as $guild => $data) {
            $this->render('guildOverview.html.twig', $guild . '/index.html', [
               'guildName' => $guild,
               'guild' => $data,
            ]);

            foreach ($data['member'] as $memberName => $membeData) {

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

            $output = $this->twig->render($template, $data);
            file_put_contents($this->distPath . $outfile, $output);
        } catch (\Twig_Error_Loader $e) {
        } catch (\Twig_Error_Runtime $e) {
        } catch (\Twig_Error_Syntax $e) {
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