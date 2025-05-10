<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 14.09.17
 * Time: 10:50
 */

namespace AppBundle\Command;


use AppBundle\Entity\Activity;
use AppBundle\Entity\InternalTicket;
use AppBundle\Service\ActivityService;
use AppBundle\Service\BuyService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\EmailService;
use AppBundle\Service\InternalTicketGeneratorService;
use AppBundle\Service\InternalTicketService;
use AppBundle\Service\OrderService;
use AppBundle\Service\UploadService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuyOrderCommand extends Command
{
	const JOB = "eurotours:order:buy";
	const ORDER = "order";

	/**
	 * @var OrderService
	 */
	private $orderService;
	/**
	 * @var BuyService
	 */
	private $buyService;
	/**
	 * @var EmailService
	 */
	private $emailService;
	/**
	 * @var InternalTicketGeneratorService
	 */
	private $internalTicketGeneratorService;
	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;
	/**
	 * @var ActivityService
	 */
	private $activityService;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var UploadService
	 */
	private $uploadService;

	public function __construct(OrderService $orderService,
	                            BuyService $buyService,
	                            EmailService $emailService,
	                            InternalTicketGeneratorService $internalTicketGeneratorService,
	                            InternalTicketService $internalTicketService,
	                            ActivityService $activityService,
	                            CurrencyService $currencyService,
	                            UploadService $uploadService)
	{
		parent::__construct();

		$this->orderService = $orderService;
		$this->buyService = $buyService;
		$this->emailService = $emailService;
		$this->internalTicketGeneratorService = $internalTicketGeneratorService;
		$this->internalTicketService = $internalTicketService;
		$this->activityService = $activityService;
		$this->currencyService = $currencyService;
		$this->uploadService = $uploadService;
	}

	protected function configure()
	{
		$this->setName(self::JOB);
		$this->addArgument(self::ORDER, InputArgument::REQUIRED);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 * @throws \Twig\Error\Error
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$order = $this->orderService->getOrder($input->getArgument(self::ORDER));
		$this->buyService->buyOrder($order);

		$order->getInternalTickets()->map(function (InternalTicket $internalTicket) use ($order) {
			$internalTicket->countPrices();
			if ($internalTicket->getCurrency() !== $order->getTicketCurrency()) {
				$priceCurrency = $this->currencyService->currencyConvert(
					$internalTicket->getPriceCurrency(), $order->getTicketCurrency()
				);
				$priceCurrencyIncludeSurcharge = $this->currencyService->currencyConvert(
					$internalTicket->getPriceCurrencyIncludeSurcharge(), $order->getTicketCurrency()
				);
				$sellerFeeCurrency = $this->currencyService->currencyConvert(
					$internalTicket->getSellerFeeCurrency(), $order->getTicketCurrency()
				);
				$internalTicket->setCurrency($priceCurrency->getCurrency());
				$internalTicket->setPrice($priceCurrency->getPrice());
				$internalTicket->setPriceIncludeSurcharge($priceCurrencyIncludeSurcharge->getPrice());
				$internalTicket->setSellerFee($sellerFeeCurrency->getPrice());
			}
			$this->internalTicketGeneratorService->generateTicket($internalTicket);
			$this->internalTicketService->saveInternalTicket($internalTicket);

			chmod($this->uploadService->getWebDir() . $internalTicket->getFile(), 0777);
		});

		// TODO: Jak vygenerovani externich jizdenek? Asi by taky melo bejt logovane. Jeste nikde neni?
		if (!$order->getInternalTickets()->isEmpty()) {
			$activity = Activity::create($order, Activity::TYPE_INTERNAL_TICKET_GENERATED);
			$activity->setContentText("Vygenerovány {$order->getInternalTicketsWithoutDuplicities()->count()} jízdenky a zaslány na e-mail {$order->getEmail()}.");
			$this->activityService->saveActivity($activity);
		}

		if (!$order->getTickets()->isEmpty()) {
			$this->emailService->sendTickets($order);
		}
	}
}
