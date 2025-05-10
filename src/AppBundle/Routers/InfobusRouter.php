<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 19.06.17
 * Time: 10:52
 */

namespace AppBundle\Routers;


use AppBundle\Connectors\InfobusConnector;
use AppBundle\Entity\BookInfobus;
use AppBundle\Entity\BookNikolo;
use AppBundle\Entity\ExternalCityInfobus;
use AppBundle\Entity\ExternalCityNikoloBusSystem;
use AppBundle\Entity\ExternalStationInfobus;
use AppBundle\Entity\ExternalTariffInfobus;
use AppBundle\Entity\ExternalTariffNikolo;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\SearchExternal;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalStationService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\Service\RouteTariffService;
use AppBundle\VO\ExternalRouteNikolo;
use AppBundle\VO\ExternalRouter;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use Doctrine\Common\Collections\ArrayCollection;

class InfobusRouter extends BusSystemRouter
{
	const EXTERNAL_ROUTER_CLASS = ExternalRouter::INFOBUS;
	const CARRIER_CODE = "Inf";

	/**
	 * @return null|string
	 */
	protected function getExternalRouterClass()
	{
		return self::EXTERNAL_ROUTER_CLASS;
	}

	/**
	 * @return null|string
	 */
	protected function getCarriedCode()
	{
		return self::CARRIER_CODE;
	}

	/**
	 * @return ExternalCityInfobus
	 */
	protected function createExternalCity()
	{
		return new ExternalCityInfobus();
	}

	/**
	 * @return ExternalStationInfobus
	 */
	protected function createExternalStation()
	{
		return new ExternalStationInfobus;
	}

	/**
	 * @return ExternalTariffInfobus
	 */
	protected function createExternalTariff()
	{
		return new ExternalTariffInfobus;
	}

	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 * @return bool
	 */
	public function canBuyOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$book = $orderPersonRouteTariff->getBook();
		if ($book instanceof BookInfobus and $book->getTicketIdentifier() !== null) {
			return true;
		}

		return false;
	}

	public function canBuyRoute(Route $route)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("Inf")) return false;

		return true;
	}
}
