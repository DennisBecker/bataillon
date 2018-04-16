<?php


namespace Bataillon\Clients;


use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;

class SWGoH
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCharacters()
    {
        return $this->client->request('GET', '/api/characters')->getBody();
    }

    public function getShips()
    {
        return $this->client->request('GET', '/api/ships')->getBody();
    }

    public function getGuildData($guildUri)
    {
        return $this->client->request('GET', $guildUri)->getBody();
    }
}