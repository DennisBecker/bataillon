<?php


namespace Bataillon\Commands;

use Bataillon\Controller\GuildDataController;
use Bataillon\Controller\UpdateController;
use Bataillon\Persistance\FileHandler;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class BuildStaticPagesCommand
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

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(OutputInterface $output, ContainerInterface $container)
    {
        $this->output = $output;
        $guildData = $container->call(GuildDataController::class);

        $fileHandler = new FileHandler();
        $fileHandler->clearDirectory($this->distPath);

        $raids = json_decode($fileHandler->read('raids.json'), true);
        $characters = [];
        foreach (json_decode($fileHandler->read('characters.json'), true) as $char) {
            $characters[$char['base_id']] = $char;
        }

        $this->render('index.html.twig', 'index.html', ['guilds' => $guildData]);

        foreach ($guildData as $guild => $data) {
            $this->render('guildOverview.html.twig', $guild . '/index.html', [
                'guilds' => $guildData,
                'activeGuild' => $guild,
                'guildName' => $guild,
                'guild' => $data,
            ]);

            foreach ($raids as $raid => $teams) {
                $this->render('raids/sithOverview.html.twig', $guild . '/' . $raid . '.html', [
                    'guilds' => $guildData,
                    'activeGuild' => $guild,
                    'guildName' => $guild,
                    'characters' => $characters,
                    'members' => $data['member'],
                    'raid' => $raid,
                    'raidTeams' => $teams,
                ]);
            }

            foreach ($data['member'] as $memberName => $memberData) {
                $this->render('memberOverview.html.twig', $guild . '/' . $memberName . '/index.html', [
                    'guilds' => $guildData,
                    'activeGuild' => $guild,
                    'memberName' => $memberName,
                    'characters' => $memberData['characters'],
                    'raids' => $raids,
                ]);

                foreach ($raids as $raid => $teams) {
                    $this->render('raids/' . $raid . '.html.twig', $guild . '/' . $memberName . '/' . $raid . '.html', [
                        'guilds' => $guildData,
                        'activeGuild' => $guild,
                        'memberName' => $memberName,
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
}