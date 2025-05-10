<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 10.07.17
 * Time: 17:11
 */

namespace AppBundle\Service;


use AppBundle\Entity\LineStation;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteCombination;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\RouteTariffCombination;
use AppBundle\Repository\RouteCombinationRepository;
use AppBundle\Repository\RouteTariffCombinationRepository;
use AppBundle\VO\PriceCurrency;
use Doctrine\Common\Collections\ArrayCollection;

class RouteCombinationService
{
	/**
	 * @var RouteCombinationRepository
	 */
	private $routeCombinationRepository;
	/**
	 * @var RouteTariffCombinationRepository
	 */
	private $routeTariffCombinationRepository;
	private CurrencyService $currencyService;

	public function __construct(RouteCombinationRepository $routeCombinationRepository,
	                            RouteTariffCombinationRepository $routeTariffCombinationRepository,
								CurrencyService $currencyService)
	{
		$this->routeCombinationRepository = $routeCombinationRepository;
		$this->routeTariffCombinationRepository = $routeTariffCombinationRepository;
		$this->currencyService = $currencyService;
	}

	public function saveRouteCombination(RouteCombination $routeCombination)
	{
		$this->routeCombinationRepository->save($routeCombination);
	}

	/**
	 * @param ArrayCollection|Route[] $routes
	 * @return ArrayCollection|RouteCombination[]
	 */
	public function createCombinations($routes)
	{
		$combinations = new ArrayCollection;

		$routes->map(function (Route $r1) use ($routes, $combinations) {
			if ($r1->getDirection() != LineStation::DIRECTION_THERE) return;

			$routes->map(function (Route $r2) use ($r1, $combinations) {
				if ($r2->getDirection() != LineStation::DIRECTION_BACK) return;

				if ($r1 === $r2) return;
				if ($r1->getLine() !== $r2->getLine()) return; # levnejsi kombinace jsou mozne jen u stejnych linek

				$r1->getRouteTariffs()->map(function (RouteTariff $rt1) use ($r2, $combinations) {
					$r2->getRouteTariffs()->map(function (RouteTariff $rt2) use ($rt1, $combinations) {
						if ($rt1 === $rt2) return;
						if ($rt1->getFare() === null) return;
						if ($rt1->getTariff() !== $rt2->getTariff()) return;

						if ($rt1->getFare()->getPriceReturnAdd()) {
							$key = $rt1->getRoute()->getId() . ":" . $rt2->getRoute()->getId();
							$combination = $combinations->get($key);
							if ($combination == null) {
								$combination = new RouteCombination;
								$combination->setThereRoute($rt1->getRoute());
								$combination->setBackRoute($rt2->getRoute());
								$combinations->set($key, $combination);
							}
							$tariffCombination = new RouteTariffCombination;
							$tariffCombination->setThereRouteTariff($rt1);
							$tariffCombination->setBackRouteTariff($rt2);
							$price = PriceCurrency::create(
								$rt1->getFare()->getPriceReturnAdd() + $rt1->getFare()->getPrice(),
								$rt1->getTariff()->getCurrency()
							);
							$tariffCombination->setPrice(
								$this->currencyService->currencyConvert(
									$price,
									$this->currencyService->getCurrentCurrency()
								)->getPrice()
							);
							$tariffCombination->setRouteCombination($combination);
							$combination->getRouteTariffCombinations()->add($tariffCombination);
						}
					});
				});
			});
		});

		$combinations->map(function (RouteCombination $routeCombination) {
			$this->saveRouteCombination($routeCombination);
		});

		return $combinations;
	}

	/**
	 * @param Route $thereRoute
	 * @param Route $backRoute
	 * @return null|RouteCombination
	 */
	public function findRouteCombinationBetweenRoutes(Route $thereRoute, Route $backRoute)
	{
		return $this->routeCombinationRepository->findOneBy([
			"thereRoute" => $thereRoute,
			"backRoute" => $backRoute
		]);
	}
}
