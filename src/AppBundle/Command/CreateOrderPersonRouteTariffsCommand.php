<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.05.18
 * Time: 17:49
 */

namespace AppBundle\Command;


use AppBundle\Entity\Order;
use AppBundle\Service\OrderPersonRouteTariffService;
use AppBundle\Service\OrderService;
use AppBundle\VO\OrderFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateOrderPersonRouteTariffsCommand extends Command
{
	/**
	 * @var OrderService
	 */
	private $orderService;
	/**
	 * @var OrderPersonRouteTariffService
	 */
	private $orderPersonRouteTariffService;

	public function __construct(OrderService $orderService,
	                            OrderPersonRouteTariffService $orderPersonRouteTariffService)
	{
		parent::__construct();
		$this->orderService = $orderService;
		$this->orderPersonRouteTariffService = $orderPersonRouteTariffService;
	}

	protected function configure()
	{
		$this->setName("eurotours:create:orderPersonRouteTariffs");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$filter = new OrderFilter;

		array_map(function (Order $order) {
			$this->orderPersonRouteTariffService->createOrderPersonRouteTariffs($order);
		}, $this->orderService->findOrders($filter));
	}
}
