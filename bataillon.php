<?php

use Symfony\Component\Console\Output\OutputInterface;

$container = require __DIR__ . '/app/bootstrap.php';

$app = new Silly\Edition\PhpDi\Application();

$container = $app->getContainer();
$container->set(Twig_Environment::class, function () {
	$loader = new Twig_Loader_Filesystem(__DIR__ . '/src/Views');
	return new Twig_Environment($loader);
});
$container->set(\Bataillon\Renderer\MemberProfile::class,
	new \Bataillon\Renderer\MemberProfile($container->get(Twig_Environment::class))
);
$container->set(\Bataillon\Client\SWGoH::class, new \Bataillon\Client\SWGoH(
	new \GuzzleHttp\Client([
		'base_uri' => 'https://swgoh.gg',
	])
));

$app->command('update', 'Bataillon\Command\RosterCommand');
//$app->setDefaultCommand('update');

$app->run();