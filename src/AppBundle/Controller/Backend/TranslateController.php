<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.08.17
 * Time: 12:26
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Service\CurrencyService;
use AppBundle\Service\LanguageService;
use AppBundle\VO\PriceCurrency;
use AppBundle\Widget\Backend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.frontend.translate")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class TranslateController
{
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;

	public function __construct(CurrencyService $currencyService,
	                            Router $router,
	                            MenuWidget $menuWidget)
	{
		$this->currencyService = $currencyService;
		$this->router = $router;
		$this->menuWidget = $menuWidget;
	}

	/**
	 * @Route(path="/backend/translates", name="backend_translates", methods={"GET"})
	 * @Template()
	 */
	public function translatesAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_TRANSLATES);

		$mainCurrency = $this->currencyService->getMainCurrency();

		$combinations = [];
		array_map(function ($currency) use (&$combinations, $mainCurrency) {
			if ($currency === $mainCurrency) return;
			$combinations[$mainCurrency . "_" . $currency] = round(
				1 / $this->currencyService->getCurrencyConvertValue(
					$mainCurrency, $currency
				),
			2);
			$combinations[$currency . "_" . $mainCurrency] = round(
				$this->currencyService->getCurrencyConvertValue(
					$currency, $mainCurrency
				),
			2);
		}, PriceCurrency::currencies());

		return $combinations;
	}

	/**
	 * @Route(path="/backend/translates", name="backend_translates_save", methods={"POST"})
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function translatesSaveAction(Request $request)
	{
		$saveFn = function (Request $request, string $leftCurrency, string $rightCurrency) {
			$this->currencyService->setCurrencyConvertValue(
				$rightCurrency, $leftCurrency,
				1/((float) str_replace(",", ".", $request->request->get($rightCurrency . "-" . $leftCurrency)))
			);

			$this->currencyService->setCurrencyConvertValue(
				$leftCurrency, $rightCurrency,
				(float) str_replace(",", ".", $request->request->get($leftCurrency . "-" . $rightCurrency))
			);
		};

		$saveFn($request,PriceCurrency::EUR, $this->currencyService->getMainCurrency());
		$saveFn($request,PriceCurrency::PLN, $this->currencyService->getMainCurrency());
		$saveFn($request,PriceCurrency::UAH, $this->currencyService->getMainCurrency());

		return RedirectResponse::create($this->router->generate("backend_translates"));
	}
}
