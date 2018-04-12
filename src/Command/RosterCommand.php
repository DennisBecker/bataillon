<?php


namespace Bataillon\Command;

use Bataillon\Client\SWGoH;
use Bataillon\Renderer\MemberProfile;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RosterCommand
{
	public function __invoke(OutputInterface $output, ContainerInterface $container)
	{
		$output->writeln("Roster Command!");

		$container->call(MemberProfile::class, []);
		$swgohClient = $container->get(SWGoH::class);
		$swgohClient->getMembers();
	}
}