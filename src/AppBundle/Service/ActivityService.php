<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-06-03
 * Time: 18:07
 */

namespace AppBundle\Service;


use AppBundle\Entity\Activity;
use AppBundle\Entity\Order;
use AppBundle\Repository\ActivityRepository;

class ActivityService
{
	/**
	 * @var ActivityRepository
	 */
	private $activityRepository;

	public function __construct(ActivityRepository $activityRepository)
	{
		$this->activityRepository = $activityRepository;
	}

	public function saveActivity(Activity $activity)
	{
		$this->activityRepository->save($activity);
	}

	/**
	 * @param Order $order
	 * @return Activity[]
	 */
	public function findActivitiesForOrder(Order $order)
	{
		return $this->activityRepository->findBy([ "order" => $order ], [  "datetimeCreated" => "DESC" ]);
	}
}
