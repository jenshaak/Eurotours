<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 30.01.18
 * Time: 14:32
 */

namespace AppBundle\Command;


use AppBundle\Routers\BlablaRouter;
use AppBundle\Routers\EastExpressRouter;
use AppBundle\Routers\FlixbusRouter;
use AppBundle\Routers\InfobusRouter;
use AppBundle\Routers\LikeBusRouter;
use AppBundle\Routers\NikoloRouter;
use AppBundle\Routers\RegabusRouter;
use AppBundle\Routers\StudentAgencyRouter;
use AppBundle\Routers\TransTempoRouter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncExternalsCommand extends Command
{
	/**
	 * @var EastExpressRouter
	 */
	private $eastExpressRouter;
	/**
	 * @var StudentAgencyRouter
	 */
	private $studentAgencyRouter;
	/**
	 * @var FlixbusRouter
	 */
	private $flixbusRouter;
	/**
	 * @var InfobusRouter
	 */
	private $infobusRouter;
	/**
	 * @var NikoloRouter
	 */
	private $nikoloRouter;
	/**
	 * @var RegabusRouter
	 */
	private $regabusRouter;
	/**
	 * @var TransTempoRouter
	 */
	private $transTempoRouter;

	private LikeBusRouter $likeBusRouter;

	private BlablaRouter $blablaRouter;

	public function __construct(EastExpressRouter $eastExpressRouter,
	                            StudentAgencyRouter $studentAgencyRouter,
	                            FlixbusRouter $flixbusRouter,
	                            InfobusRouter $infobusRouter,
	                            NikoloRouter $nikoloRouter,
	                            RegabusRouter $regabusRouter,
	                            TransTempoRouter $transTempoRouter,
								LikeBusRouter $likeBusRouter,
								BlablaRouter $blablaRouter)
	{
		$this->eastExpressRouter = $eastExpressRouter;
		$this->studentAgencyRouter = $studentAgencyRouter;
		$this->flixbusRouter = $flixbusRouter;
		$this->infobusRouter = $infobusRouter;
		$this->nikoloRouter = $nikoloRouter;
		$this->regabusRouter = $regabusRouter;
		$this->transTempoRouter = $transTempoRouter;
		$this->likeBusRouter = $likeBusRouter;
		$this->blablaRouter = $blablaRouter;

		parent::__construct();
	}

	protected function configure()
	{
		$this->setName("eurotours:externals:sync");
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws \ErrorException
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		# TODO: dodelat i sync dalsich Rout. Ale zkontrolovat jestli je lze opravdu spoustet opakovane.

		$this->blablaRouter->syncExternals();

		/*


		$this->infobusRouter->syncExternals();
		$this->nikoloRouter->syncExternals();
		$this->transTempoRouter->syncExternals();
		//$this->regabusRouter->syncExternals();
		//$this->flixbusRouter->syncExternals();
		$this->eastExpressRouter->syncExternals();
		$this->likeBusRouter->syncExternals();*/
	}
}
