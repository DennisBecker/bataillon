<?php


namespace Bataillon\Renderer;


class MemberProfile
{
	/**
	 * @var \Twig_Environment
	 */
	private $twig;

	public function __construct(\Twig_Environment $twig)
	{
		$this->twig = $twig;
	}

	public function __invoke()
	{
		var_dump("yeah");
	}
}