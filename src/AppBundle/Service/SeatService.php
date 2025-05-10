<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.02.18
 * Time: 15:58
 */

namespace AppBundle\Service;


use AppBundle\Entity\Seat;
use AppBundle\Repository\SeatRepository;

class SeatService
{
	/**
	 * @var SeatRepository
	 */
	private $seatRepository;

	public function __construct(SeatRepository $seatRepository)
	{
		$this->seatRepository = $seatRepository;
	}

	public function saveSeat(Seat $seat)
	{
		$this->seatRepository->save($seat);
	}

}
