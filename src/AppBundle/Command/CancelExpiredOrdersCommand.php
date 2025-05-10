<?php


namespace AppBundle\Command;


use AppBundle\Entity\Activity;
use AppBundle\Entity\Book;
use AppBundle\Entity\Order;
use AppBundle\Service\ActivityService;
use AppBundle\Service\BookService;
use AppBundle\Service\EmailService;
use AppBundle\Service\OrderService;
use AppBundle\VO\OrderFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CancelExpiredOrdersCommand extends Command
{
	/**
	 * @var OrderService
	 */
	private $orderService;
	/**
	 * @var BookService
	 */
	private $bookService;
	/**
	 * @var EmailService
	 */
	private $emailService;
	/**
	 * @var ActivityService
	 */
	private $activityService;

	public function __construct(OrderService $orderService,
	                            BookService $bookService,
	                            EmailService $emailService,
	                            ActivityService $activityService)
	{
		parent::__construct();
		$this->orderService = $orderService;
		$this->bookService = $bookService;
		$this->emailService = $emailService;
		$this->activityService = $activityService;
	}

	protected function configure()
	{
		$this->setName("eurotours:orders:cancelExpired");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$orderFilter = new OrderFilter;
		$orderFilter->setOnlyExpired(true);
		$orderFilter->setOnlyNonDraft(true);
		$orderFilter->setOnlyNonCancelled(true);
		$orderFilter->setStatus(Order::STATUS_RESERVATION);
		$orders = $this->orderService->findOrders($orderFilter);

		array_map(function (Order $order) use ($output) {
			$output->writeln("Expirace {$order->getIdent()} z {$order->getDatetimeCreated()->format('c')}");
			$order->setCancelled(true);
			$order->setDatetimeCancelled(new \DateTime);
			$this->orderService->saveOrder($order);

			$order->getBooks()->map(function (Book $book) {
				$book->setCancelled(true);
				$book->setDatetimeCancelled(new \DateTime);
				$this->bookService->saveBook($book);
			});

			$activity = Activity::create($order, Activity::TYPE_EXPIRED_BOOK);
			$activity->setContentText("Rezervace expirována, stornována.");
			$this->activityService->saveActivity($activity);
		}, $orders);

		if (!empty($orders)) {
			$this->emailService->sendExpiredOrders($orders);
		}
	}
}
