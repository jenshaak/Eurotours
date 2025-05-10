<?php


namespace AppBundle\Command;


use AppBundle\Entity\SeoCityCombination;
use AppBundle\Service\CityService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\Service\SeoCityCombinationService;
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeoPagesCommand extends Command
{
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var SeoCityCombinationService
	 */
	private $seoCityCombinationService;
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(RouteService $routeService,
	                            EntityManagerInterface $entityManager,
	                            CityService $cityService,
	                            SeoCityCombinationService $seoCityCombinationService,
	                            LanguageService $languageService)
	{
		parent::__construct();

		$this->routeService = $routeService;
		$this->entityManager = $entityManager;
		$this->cityService = $cityService;
		$this->seoCityCombinationService = $seoCityCombinationService;
		$this->languageService = $languageService;
	}

	protected function configure()
	{
		$this->setName("eurotours:seo");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$connection = $this->entityManager->getConnection();
		$sql = "SELECT route_id routeId, from_city_id fromCityId, to_city_id toCityId, DATE_FORMAT(datetime_departure, '%k:%i') timeDeparture, DATE_FORMAT(datetime_arrival, '%k:%i') timeArrival FROM routes WHERE datetime_departure > DATE_SUB(NOW(), INTERVAL 1 YEAR)";
		$query = $connection->prepare($sql);
		$query->execute();

		$combinations = [];

		array_map(function ($combination) use (&$combinations) {
			if ($combination->fromCityId === null or $combination->toCityId === null) return;
			$key = "{$combination->fromCityId}:{$combination->toCityId}";
			if (!isset($combinations[$key])) {
				$combinations[$key] = (object) [
					"fromCityId" => $combination->fromCityId,
					"toCityId" => $combination->toCityId,
					"routes" => []
				];
			}
			$combinations[$key]->routes["{$combination->timeDeparture}"] = $combination->routeId;
		}, $query->fetchAll(PDO::FETCH_OBJ));

		array_map(function ($c) {
			$fromCity = $this->cityService->getCity($c->fromCityId);
			$toCity = $this->cityService->getCity($c->toCityId);
			$combination = $this->seoCityCombinationService->findSeoCityCombination($fromCity, $toCity);
			if ($combination === null) {
				$combination = new SeoCityCombination;
				$combination->setFromCity($fromCity);
				$combination->setToCity($toCity);
				$combination->setFromSlug($this->slugify($fromCity->getName()->getString($this->languageService->getEnglish())));
				$combination->setToSlug($this->slugify($toCity->getName()->getString($this->languageService->getEnglish())));
			}
			$combination->setRoutes($c->routes);
			$this->seoCityCombinationService->saveSeoCityCombination($combination);

		}, $combinations);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	private function slugify($text)
	{
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = trim($text, '-');
		$text = preg_replace('~-+~', '-', $text);
		$text = strtolower($text);

		return $text;
	}
}
