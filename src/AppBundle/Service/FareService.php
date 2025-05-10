<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.04.17
 * Time: 0:00
 */

namespace AppBundle\Service;


use AppBundle\Entity\Fare;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Tariff;
use AppBundle\Repository\FareRepository;

class FareService
{
	/**
	 * @var FareRepository
	 */
	private $fareRepository;

	public function __construct(FareRepository $fareRepository)
	{
		$this->fareRepository = $fareRepository;
	}

	public function saveFare(Fare $fare)
	{
		$this->fareRepository->save($fare);
	}

	public function saveFareWithoutFlush(Fare $fare)
	{
		$this->fareRepository->save($fare, false);
	}

	/**
	 * @param int $id
	 * @return null|Fare
	 */
	public function getFare($id)
	{
		return $this->fareRepository->find($id);
	}

	/**
	 * @param Fare $fare
	 */
	public function removeFare(Fare $fare)
	{
		$this->fareRepository->remove($fare);
	}

	/**
	 * @param LineStation $fromLineStation
	 * @param LineStation $toLineStation
	 * @param Tariff $tariff
	 * @return null|Fare
	 */
	public function getFareByLineStations(LineStation $fromLineStation, LineStation $toLineStation, Tariff $tariff)
	{
		return $this->fareRepository->findOneBy([
			"fromLineStation" => $fromLineStation,
			"toLineStation" => $toLineStation,
			"tariff" => $tariff,
			"deleted" => false
		]);
	}

}
