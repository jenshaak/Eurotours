<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 15:27
 */

namespace AppBundle\Command;


use AppBundle\Routers\BlablaRouter;
use AppBundle\Routers\EastExpressRouter;
use AppBundle\Routers\EurolinesRouter;
use AppBundle\Routers\FlixbusRouter;
use AppBundle\Routers\InfobusRouter;
use AppBundle\Routers\LikeBusRouter;
use AppBundle\Routers\NikoloRouter;
use AppBundle\Routers\RegabusRouter;
use AppBundle\Routers\StudentAgencyRouter;
use AppBundle\Routers\TransTempoRouter;
use AppBundle\Service\SearchExternalService;
use AppBundle\VO\ExternalRouter;
use Predis\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessAdaptersCommand extends Command
{
	const ARGUMENT_TYPE = "type";
	/**
	 * @var StudentAgencyRouter
	 */
	private $studentAgencyRouter;
	/**
	 * @var Client
	 */
	private $redis;

	/**
	 * @var SearchExternalService
	 */
	private $searchExternalService;
	/**
	 * @var InfobusRouter
	 */
	private $infobusRouter;
	/**
	 * @var EastExpressRouter
	 */
	private $eastExpressRouter;
	/**
	 * @var FlixbusRouter
	 */
	private $flixbusRouter;
	/**
	 * @var EurolinesRouter
	 */
	private $eurolinesRouter;
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

	public function __construct(StudentAgencyRouter $studentAgencyRouter,
	                            Client $redis,
	                            SearchExternalService $searchExternalService,
	                            InfobusRouter $infobusRouter,
	                            EastExpressRouter $eastExpressRouter,
	                            FlixbusRouter $flixbusRouter,
	                            EurolinesRouter $eurolinesRouter,
	                            NikoloRouter $nikoloRouter,
	                            RegabusRouter $regabusRouter,
	                            TransTempoRouter $transTempoRouter,
								LikeBusRouter $likeBusRouter,
								BlablaRouter $blablaRouter)
	{
		$this->studentAgencyRouter = $studentAgencyRouter;
		$this->redis = $redis;
		$this->searchExternalService = $searchExternalService;
		$this->infobusRouter = $infobusRouter;
		$this->eastExpressRouter = $eastExpressRouter;
		$this->flixbusRouter = $flixbusRouter;
		$this->eurolinesRouter = $eurolinesRouter;
		$this->nikoloRouter = $nikoloRouter;
		$this->regabusRouter = $regabusRouter;
		$this->transTempoRouter = $transTempoRouter;
		$this->likeBusRouter = $likeBusRouter;
		$this->blablaRouter = $blablaRouter;

		parent::__construct();
	}

	protected function configure()
	{
		$this->setName("eurotours:process:adapters");
		$this->addArgument(self::ARGUMENT_TYPE, InputArgument::REQUIRED);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 * @throws \ErrorException
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$type = $input->getArgument(self::ARGUMENT_TYPE);

		$iteration = 0;
		$maxIteration = random_int(600, 800);

		while ($iteration++ < $maxIteration) {
			$searchExternalId = $this->redis->rpop($type);
			if ($searchExternalId === null) {
				usleep(500000);
				continue;
			}

			$searchExternal = $this->searchExternalService->getSearchExternal($searchExternalId);

			if ($searchExternal === null) continue;

			if ($type === ExternalRouter::STUDENT_AGENCY) {
				$this->studentAgencyRouter->findRoutes($searchExternal);
			} elseif ($type === ExternalRouter::INFOBUS) {
				$this->infobusRouter->findRoutes($searchExternal);
			} elseif ($type === ExternalRouter::EAST_EXPRESS) {
				$this->eastExpressRouter->findRoutes($searchExternal);
			} elseif ($type === ExternalRouter::FLIXBUS) {
				$this->flixbusRouter->findRoutes($searchExternal);
			} elseif ($type === ExternalRouter::EUROLINES) {
				$this->eurolinesRouter->findRoutes($searchExternal);
			} elseif ($type === ExternalRouter::NIKOLO) {
				$this->nikoloRouter->findRoutes($searchExternal);
			} elseif ($type === ExternalRouter::BLABLA) {
				$this->blablaRouter->findRoutes($searchExternal);
			} elseif ($type === ExternalRouter::TRANS_TEMPO) {
				$this->transTempoRouter->findRoutes($searchExternal);
			} elseif ($type === ExternalRouter::LIKEBUS) {
				$this->likeBusRouter->findRoutes($searchExternal);
			}

			$searchExternal->setProcessed(true);
			$searchExternal->setDatetimeProcessed(new \DateTime);
			$this->searchExternalService->saveSearchExternal($searchExternal);
		}
	}
}
