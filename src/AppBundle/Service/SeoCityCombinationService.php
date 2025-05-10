<?php


namespace AppBundle\Service;


use AppBundle\Entity\City;
use AppBundle\Entity\SeoCityCombination;
use AppBundle\Repository\SeoCityCombinationRepository;

class SeoCityCombinationService
{
	/**
	 * @var SeoCityCombinationRepository
	 */
	private $seoCityCombinationRepository;

	public function __construct(SeoCityCombinationRepository $seoCityCombinationRepository)
	{
		$this->seoCityCombinationRepository = $seoCityCombinationRepository;
	}

	public function saveSeoCityCombination(SeoCityCombination $seoCityCombination)
	{
		$this->seoCityCombinationRepository->save($seoCityCombination);
	}

	/**
	 * @param City $fromCity
	 * @param City $toCity
	 * @return SeoCityCombination|object|null
	 */
	public function findSeoCityCombination(City $fromCity, City $toCity)
	{
		return $this->seoCityCombinationRepository->findOneBy([ "fromCity" => $fromCity, "toCity" => $toCity ]);
	}

	/**
	 * @param City $fromCity
	 * @param City $toCity
	 * @return SeoCityCombination[]
	 */
	public function findSeoCitiesCombinations(City $fromCity)
	{
		return $this->seoCityCombinationRepository->findBy([ "fromCity" => $fromCity ]);
	}
}
