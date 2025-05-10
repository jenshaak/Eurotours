<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 14.11.17
 * Time: 20:01
 */

namespace AppBundle\Twig;


use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DomainExtension extends \Twig_Extension
{
	/**
	 * @var Container
	 */
	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction("domain", [ $this, "getDomain" ]),
		];
	}

	public function getName()
	{
		return "domain";
	}

	/**
	 * @return string
	 */
	public function getDomain()
	{
		return $this->container->getParameter("domain");
	}
}
