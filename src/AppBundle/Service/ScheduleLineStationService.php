<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.05.17
 * Time: 15:57
 */

namespace AppBundle\Service;


use AppBundle\Entity\ScheduleLineStation;
use AppBundle\Repository\ScheduleLineStationRepository;

class ScheduleLineStationService
{
	/**
	 * @var ScheduleLineStationRepository
	 */
	private $scheduleLineStationRepository;

	public function __construct(ScheduleLineStationRepository $scheduleLineStationRepository)
	{
		$this->scheduleLineStationRepository = $scheduleLineStationRepository;
	}

	/**
	 * @param ScheduleLineStation $scheduleLineStation
	 */
	public function saveScheduleLineStation(ScheduleLineStation $scheduleLineStation)
	{
		$this->scheduleLineStationRepository->save($scheduleLineStation);
	}

	/**
	 * @param ScheduleLineStation $scheduleLineStation
	 */
	public function removeScheduleLineStation(ScheduleLineStation $scheduleLineStation)
	{
		$scheduleLineStation->getLineStation()->getScheduleLineStations()->removeElement($scheduleLineStation);
		$this->scheduleLineStationRepository->remove($scheduleLineStation);
	}

}