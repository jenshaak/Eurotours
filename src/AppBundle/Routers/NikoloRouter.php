<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.18
 * Time: 16:12
 */

namespace AppBundle\Routers;


use AppBundle\Entity\BookNikolo;
use AppBundle\Entity\ExternalCityNikolo;
use AppBundle\Entity\ExternalCityNikoloBusSystem;
use AppBundle\Entity\ExternalStationNikolo;
use AppBundle\Entity\ExternalTariffNikolo;
use AppBundle\Entity\ExternalTicketNikolo;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\VO\ExternalRouteNikolo;
use AppBundle\VO\ExternalRouter;
use AppBundle\VO\NikoloSellTicket;

class NikoloRouter extends BusSystemRouter
{
	const EXTERNAL_ROUTER_CLASS = ExternalRouter::NIKOLO;
	const CARRIER_CODE = "NikoloAPI";

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
	 * @return ExternalCityNikoloBusSystem
	 */
	protected function createExternalCity()
	{
		return new ExternalCityNikoloBusSystem();
	}

	protected function createExternalStation()
	{
		return new ExternalStationNikolo;
	}

	/**
	 * @return ExternalRouteNikolo
	 */
	protected function createExternalRoute()
	{
		return new ExternalRouteNikolo;
	}

	/**
	 * @return ExternalTariffNikolo
	 */
	protected function createExternalTariff()
	{
		return new ExternalTariffNikolo;
	}

	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 * @return bool
	 */
	public function canBuyOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$book = $orderPersonRouteTariff->getBook();
		if ($book instanceof BookNikolo and $book->getTicketIdentifier() !== null) {
			return true;
		}

		return false;
	}

	public function canBuyRoute(Route $route)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("NikoloAPI")) return false;

		return true;
	}
}
