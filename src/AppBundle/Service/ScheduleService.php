<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 17:29
 */

namespace AppBundle\Service;


use AppBundle\Entity\Schedule;
use AppBundle\Entity\ScheduleLineStation;
use AppBundle\Repository\ScheduleRepository;
use AppBundle\VO\DuplicatorVO;
use AppBundle\VO\ScheduleFilter;

class ScheduleService
{
	/**
	 * @var ScheduleRepository
	 */
	private $scheduleRepository;

	public function __construct(ScheduleRepository $scheduleRepository)
	{
		$this->scheduleRepository = $scheduleRepository;
	}

	/**
	 * @param Schedule $schedule
	 */
	public function saveSchedule(Schedule $schedule)
	{
		$this->scheduleRepository->save($schedule);
	}

	/**
	 * @param Schedule $schedule
	 */
	public function removeSchedule(Schedule $schedule)
	{
		$schedule->setDeleted(true);
		$this->saveSchedule($schedule);
	}

	/**
	 * @param Schedule $oldSchedule
	 * @return Schedule
	 */
	public function duplicateSchedule(Schedule $oldSchedule)
	{
		$duplicator = new DuplicatorVO;

		/** @var Schedule $schedule */
		$schedule = $duplicator->duplicate($oldSchedule);
		$schedule->setScheduleLineStations($duplicator->processArrayCollection($schedule->getScheduleLineStations()));
		$schedule->getScheduleLineStations()->map(function (ScheduleLineStation $scheduleLineStation) use ($duplicator) {
			$scheduleLineStation->setSchedule($duplicator->duplicate($scheduleLineStation->getSchedule()));
		});
		$schedule->setWeight($oldSchedule->getWeight() + 20);

		return $schedule;
	}
}
