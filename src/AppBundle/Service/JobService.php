<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 14.09.17
 * Time: 10:34
 */

namespace AppBundle\Service;


use AppBundle\Command\BuyOrderCommand;
use AppBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;
use JMS\JobQueueBundle\Entity\Repository\JobRepository;

class JobService
{
	/**
	 * @var JobRepository
	 */
	private $jobRepository;
	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function __construct(JobRepository $jobRepository,
	                            EntityManager $entityManager)
	{
		$this->jobRepository = $jobRepository;
		$this->entityManager = $entityManager;
	}

	public function getBuyOrderJob(Order $order)
	{
		$this->jobRepository->findAllForRelatedEntity($order);
	}

	/**
	 * @param Order $order
	 */
	public function buyOrder(Order $order)
	{
		$job = new Job(BuyOrderCommand::JOB, [ $order->getId() ]);
		$job->addRelatedEntity($order);
		$this->entityManager->persist($job);
		$this->entityManager->flush();
	}
}
