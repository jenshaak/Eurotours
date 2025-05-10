<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.05.17
 * Time: 17:18
 */

namespace AppBundle\Service;


use AppBundle\Entity\RouteTariff;
use AppBundle\Repository\RouteTariffRepository;

class RouteTariffService
{
	/**
	 * @var RouteTariffRepository
	 */
	private $routeTariffRepository;

	public function __construct(RouteTariffRepository $routeTariffRepository)
	{
		$this->routeTariffRepository = $routeTariffRepository;
	}

	/**
	 * @param RouteTariff $routeTariff
	 */
	public function saveRouteTariff(RouteTariff $routeTariff)
	{
		$this->routeTariffRepository->save($routeTariff);
	}

	/**
	 * @param int $id
	 * @return null|RouteTariff
	 */
	public function getRouteTariff($id)
	{
		return $this->routeTariffRepository->find($id);
	}
}