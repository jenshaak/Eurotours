<?php


namespace AppBundle\Command;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanDatabaseCommand extends Command
{
	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function __construct(EntityManager $entityManager)
	{
		parent::__construct();
		$this->entityManager = $entityManager;
	}

	protected function configure()
	{
		$this->setName("eurotours:clean:database");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$excludeSearchExternalId = [];

		$query = $this->entityManager->getConnection()->prepare("SELECT search_external_id s FROM routes WHERE search_external_id IS NOT NULL");
		$query->execute();
		$excludeSearchExternalId = array_merge($excludeSearchExternalId, array_map(function ($s) { return $s['s']; }, $query->fetchAll()));

		$query = $this->entityManager->getConnection()->prepare("SELECT search_external_id FROM searches_external WHERE datetime_created < DATE_SUB(NOW(), INTERVAL -3 MONTH)");
		$query->execute();
		array_map(function ($s) use ($excludeSearchExternalId) {
			$searchExternalId = $s['search_external_id'];
			if (in_array($searchExternalId, $excludeSearchExternalId)) return;

			echo ".";
			$this->entityManager->getConnection()->prepare("DELETE FROM searches_external WHERE search_external_id = '{$searchExternalId}'")->execute();

		}, $query->fetchAll());

		$excludeRouteIds = [];

		$query = $this->entityManager->getConnection()->prepare("SELECT routes FROM seo_cities_combinations");
		$query->execute();

		array_map(function ($routes) use (&$excludeRouteIds) {
			$excludeRouteIds = array_merge($excludeRouteIds, explode(",", $routes['routes']));
		}, $query->fetchAll());

		$query = $this->entityManager->getConnection()->prepare("SELECT route_id r FROM orders_persons JOIN routes_tariffs ON (orders_persons.route_tariff_there_id = routes_tariffs.route_tariff_id);");
		$query->execute();
		$excludeRouteIds = array_merge($excludeRouteIds, array_map(function ($r) { return $r['r']; }, $query->fetchAll()));

		$query = $this->entityManager->getConnection()->prepare("SELECT route_id r FROM orders_persons JOIN routes_tariffs ON (orders_persons.route_tariff_back_id = routes_tariffs.route_tariff_id);");
		$query->execute();
		$excludeRouteIds = array_merge($excludeRouteIds, array_map(function ($r) { return $r['r']; }, $query->fetchAll()));

		$query = $this->entityManager->getConnection()->prepare("SELECT route_id r FROM internal_tickets");
		$query->execute();
		$excludeRouteIds = array_merge($excludeRouteIds, array_map(function ($r) { return $r['r']; }, $query->fetchAll()));

		$query = $this->entityManager->getConnection()->prepare("SELECT routes_tariffs.route_id r FROM external_tickets JOIN routes_tariffs USING (route_tariff_id)");
		$query->execute();
		$excludeRouteIds = array_merge($excludeRouteIds, array_map(function ($r) { return $r['r']; }, $query->fetchAll()));

		$query = $this->entityManager->getConnection()->prepare("SELECT there_route_id r FROM orders");
		$query->execute();
		$excludeRouteIds = array_merge($excludeRouteIds, array_map(function ($r) { return $r['r']; }, $query->fetchAll()));

		$query = $this->entityManager->getConnection()->prepare("SELECT back_route_id r FROM orders WHERE back_route_id IS NOT NULL");
		$query->execute();
		$excludeRouteIds = array_merge($excludeRouteIds, array_map(function ($r) { return $r['r']; }, $query->fetchAll()));

		$query = $this->entityManager->getConnection()->prepare("SELECT routes_tariffs.route_id r FROM internal_tickets JOIN routes_tariffs USING (route_tariff_id)");
		$query->execute();
		$excludeRouteIds = array_merge($excludeRouteIds, array_map(function ($r) { return $r['r']; }, $query->fetchAll()));

		$query = $this->entityManager->getConnection()->prepare("SELECT route_id r FROM routes WHERE datetime_departure < DATE_SUB(NOW(), INTERVAL -1 YEAR)");
		$query->execute();
		$routeIds = array_map(function ($r) {
			return $r['r'];
		}, $query->fetchAll());

		$excludeRouteIds = array_unique($excludeRouteIds);

		array_map(function ($routeId) use ($excludeRouteIds) {
			if (in_array($routeId, $excludeRouteIds)) return;

			echo ".";
			$query = $this->entityManager->getConnection()->prepare(
				"SELECT route_combination_id FROM routes_combinations WHERE there_route_id = '{$routeId}' OR back_route_id = '{$routeId}'"
			);
			$query->execute();

			array_map(function ($routeCombinationId) {
				$routeCombinationId = $routeCombinationId['route_combination_id'];
				$this->entityManager->getConnection()->prepare("DELETE FROM routes_tariffs_combinations WHERE route_combination_id = '{$routeCombinationId}'")->execute();
				$this->entityManager->getConnection()->prepare("DELETE FROM routes_combinations WHERE route_combination_id = '{$routeCombinationId}'")->execute();
			}, $query->fetchAll());

			$this->entityManager->getConnection()->prepare("DELETE FROM routes_tariffs WHERE route_id = \"{$routeId}\"")->execute();
			$this->entityManager->getConnection()->prepare("DELETE FROM routes WHERE route_id = \"{$routeId}\"")->execute();
		}, $routeIds);


	}
}
