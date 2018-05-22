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

    /**
     * @var FileHandler
     */
    private $fileHandler;

    private $distPath = __DIR__ . '/../../dist/';

    public function __construct(\Twig_Environment $twig, FileHandler $fileHandler)
    {
        $this->twig = $twig;
        $this->fileHandler = $fileHandler;
    }

    public function __invoke(OutputInterface $output, ContainerInterface $container)
    {
        $this->output = $output;
        $this->fileHandler->clearDirectory($this->distPath);

        $dataPointData = $container->call(GuildDataController::class);

        list($currentGuildData, $comparingGuildData, $currentPlayerData, $comparingPlayerData) = [
            $dataPointData['currentGuildData'],
            $dataPointData['comparingGuildData'],
            $dataPointData['currentPlayerData'],
            $dataPointData['comparingPlayerData'],
        ];

        $raids = $this->getRaidData();
        $characters = $this->getCharacterData();

        $this->render('index.html.twig', 'index.html', [
            'guilds' => $currentGuildData,
            'comparingGuildData' => $comparingGuildData
        ]);

        foreach ($currentPlayerData as $guild => $members) {
            $this->render('guildOverview.html.twig', $guild . '/index.html', [
                'guilds' => $currentGuildData,
                'activeGuild' => $guild,
                'guildName' => $guild,
                'members' => $members,
                'comparingPlayerData' => $comparingPlayerData,
            ]);

            foreach ($raids as $raid => $teams) {
                $this->render('raids/sithOverview.html.twig', $guild . '/' . $raid . '.html', [
                    'guilds' => $currentGuildData,
                    'activeGuild' => $guild,
                    'guildName' => $guild,
                    'characters' => $characters,
                    'members' => $members,
                    'raid' => $raid,
                    'raidTeams' => $teams,
                ]);
            }
/*
            foreach ($data['member'] as $memberName => $memberData) {
                $this->render('memberOverview.html.twig', $guild . '/' . $memberName . '/index.html', [
                    'guilds' => $currentGuildData,
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
*/
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

    /**
     * @return array
     */
    protected function getRaidData(): array
    {
        return json_decode($this->fileHandler->read('raids.json'), true);
    }

    /**
     * @return array
     */
    protected function getCharacterData(): array
    {
        $characters = [];
        foreach (json_decode($this->fileHandler->read('characters.json'), true) as $char) {
            $characters[$char['base_id']] = $char;
        }
        return $characters;
    }
}