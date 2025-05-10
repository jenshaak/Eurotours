<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 17:30
 */

namespace AppBundle\Service;


use AppBundle\Entity\Fare;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\ScheduleLineStation;
use AppBundle\Entity\Tariff;
use AppBundle\Repository\LineStationRepository;

class LineStationService
{
	/**
	 * @var LineStationRepository
	 */
	private $lineStationRepository;

	public function __construct(LineStationRepository $lineStationRepository)
	{
		$this->lineStationRepository = $lineStationRepository;
	}

	/**
	 * @param int $id
	 * @return null|LineStation
	 */
	public function getLineStation($id)
	{
		return $this->lineStationRepository->find($id);
	}

	/**
	 * @param LineStation $lineStation
	 */
	public function saveLineStation(LineStation $lineStation)
	{
		$this->lineStationRepository->save($lineStation);
	}

	/**
	 * @param LineStation $lineStation
	 */
	public function deleteLineStation(LineStation $lineStation)
	{
		$this->lineStationRepository->deleteLineStation($lineStation);
	}

	public function loadAllLineStations()
	{
		$this->lineStationRepository->findBy([ "deleted" => false ]);
	}

}
