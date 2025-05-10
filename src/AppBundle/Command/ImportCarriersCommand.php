<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 19:02
 */

namespace AppBundle\Command;


use AppBundle\Entity\Carrier;
use AppBundle\Service\CarrierService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCarriersCommand extends Command
{
	/**
	 * @var CarrierService
	 */
	private $carrierService;

	public function __construct(CarrierService $carrierService)
	{
		$this->carrierService = $carrierService;
		
		parent::__construct();
	}

	protected function configure()
	{
		$this->setName("eurotours:import:carriers");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		\dibi::connect([
			'driver'   => "mysql",
			'host'     => "127.0.0.1",
			'username' => "root",
			'password' => "heslycko",
			"database" => "eurotours"
		]);

		$result = \dibi::query("SELECT id, name, nameOriginal FROM traders")->fetchAll();

		foreach ($result as $r) {
			if ($this->carrierService->getCarrier((int) $r->id) === null) {
				$carrier = new Carrier;
				$carrier->setId((int) $r->id);
				$carrier->setCode($r->name);
				$carrier->setName($r->nameOriginal);
				$this->carrierService->saveCarrier($carrier);
			}
		}

	}
}