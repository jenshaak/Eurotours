<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.08.17
 * Time: 12:03
 */

namespace AppBundle\Controller\Frontend;


use AppBundle\Service\CurrencyService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.frontend.currency")
 */
class CurrencyController
{
	const REFERER = "referer";

	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var Router
	 */
	private $router;

	public function __construct(CurrencyService $currencyService,
	                            Router $router)
	{
		$this->currencyService = $currencyService;
		$this->router = $router;
	}

	/**
	 * @Route(path="/switch/currency/{currency}", name="currency_switch")
	 * @param string $currency
	 * @param Request $request
	 * @return
	 */
	public function switchLanguageAction($currency, Request $request)
	{
		$this->currencyService->setCurrentCurrency($currency);

		$router = $request->headers->get(self::REFERER);

		return RedirectResponse::create($router ? $router : $this->router->generate("homepage"));
	}
}