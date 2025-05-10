<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:46
 */

namespace AppBundle\Service;


use AppBundle\Entity\City;
use AppBundle\Entity\Station;
use AppBundle\Repository\StationRepository;

class StationService
{
	/**
	 * @var StationRepository
	 */
	private $stationRepository;

	public function __construct(StationRepository $stationRepository)
	{
		$this->stationRepository = $stationRepository;
	}

	/**
	 * @param int $id
	 * @return null|Station
	 */
	public function getStation($id)
	{
		return $this->stationRepository->find($id);
	}

	/**
	 * @param Station $station
	 */
	public function saveStation(Station $station)
	{
		$this->stationRepository->save($station);
	}

	/**
	 * @param City $city
	 * @param int $oldStationId
	 * @return null|Station
	 */
	public function getStationByOldStation(City $city, $oldStationId)
	{
		return $this->stationRepository->findOneBy([ "city" => $city, "oldStationId" => $oldStationId ]);
	}

	/**
	 * @return Station[]
	 */
	public function findAllStations()
	{
		return $this->stationRepository->findBy([ /*"deleted" => false*/ ]);
	}
}
