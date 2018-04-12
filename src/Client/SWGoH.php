<?php


namespace Bataillon\Client;


use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;

class SWGoH
{
	private $guildUris = [
		'501st Bataillon' => '/api/guilds/15066/units/',
		'Imperial Bataillon' => '/api/guilds/21878/units/',
		'104th Bataillon' => '/api/guilds/18908/units/',
		'41st Bataillon' => '/api/guilds/3233/units/',
		'B2TF Bataillon' => '/api/guilds/25014/units/',
		'Outerrim10 Bataillon' => '/api/guilds/25118/units/',
		'313th Bataillon' => '/api/guilds/31673/units/',
		'43rd Bataillon' => '/api/guilds/32210/units/',
		'442nd Bataillon' => '/api/guilds/16040/units/',
		'18th Bataillon' => '/api/guilds/32283/units/',
	];

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

	}

	public function getShips()
	{

	}

	public function getMembers()
	{
		$guildUris = $this->guildUris;
		$requestPromises = (function () use ($guildUris) {
			foreach ($guildUris as $uri) {
				yield function() use ($uri) {
					return $this->client->getAsync($uri);
				};
			}
		})();

		$data = [];
		$pool = new Pool($this->client, $requestPromises, [
			'concurrency' => 10,
			'fulfilled' => function ($response, $index) use (&$data, $guildUris) {
				$data[array_keys($guildUris)[$index]] = (string)$response->getBody();
			},
		]);
		$promise = $pool->promise();
		$promise->wait();

		return $data;
	}
}