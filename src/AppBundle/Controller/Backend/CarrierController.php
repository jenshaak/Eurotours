<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 31.10.17
 * Time: 14:18
 */

namespace AppBundle\Controller\Backend;
use AppBundle\Entity\Carrier;
use AppBundle\Service\CarrierService;
use AppBundle\Widget\Backend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.carrier")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class CarrierController
{
	const PARAM_CODE = "code";
	const PARAM_NAME = "name";
	const PARAM_SURCHARGE = "surcharge";
	const PARAM_COMMISSION = "commission";
	const PARAM_VALUE = "value";
	const PARAM_CANT_PAY_ONLINE = "cantPayOnline";

	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var \AppBundle\Service\FlashService
	 */
	private $flashService;

	public function __construct(CarrierService $carrierService,
	                            Router $router,
	                            MenuWidget $menuWidget,
	                            \AppBundle\Service\FlashService $flashService)
	{
		$this->carrierService = $carrierService;
		$this->router = $router;
		$this->menuWidget = $menuWidget;
		$this->flashService = $flashService;

		$this->menuWidget->setActive(MenuWidget::ACTIVE_CARRIERS);
	}

	/**
	 * @Route(path="/backend/carriers", name="backend_carriers", methods={"GET"})
	 * @Template()
	 */
	public function carriersAction()
	{
		return [
			"carriers" => $this->carrierService->findAllCarriers(),
			"deletedCarriers" => $this->carrierService->findAllDeletedCarriers()
		];
	}

	/**
	 * @Route(path="/backend/carrier/create", name="backend_carrier_create", methods={"POST"})
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function carrierCreateAction(Request $request)
	{
		$code = $request->request->get(self::PARAM_CODE);
		
		if ($this->carrierService->existsCarrierWithCode($code)) {
			$this->flashService->addError(sprintf('Dopravce s kódem %s již existuje', $code));
			return RedirectResponse::create($this->router->generate("backend_carriers"));
		}
		
		$carrier = new Carrier;
		$carrier->setCode($code);
		$carrier->setName($request->request->get(self::PARAM_NAME));
		$this->carrierService->saveCarrier($carrier);

		return RedirectResponse::create($this->router->generate("backend_carriers"));
	}

	/**
	 * @Route(path="/backend/carrier/{carrier}", name="backend_carrier_save", methods={"POST"})
	 * @param Carrier $carrier
	 * @return RedirectResponse
	 */
	public function carrierSaveAction(Carrier $carrier, Request $request)
	{
		$surcharge = $request->request->getInt(self::PARAM_SURCHARGE);
		$commission = $request->request->getInt(self::PARAM_COMMISSION);
		$cantPayOnline = $request->request->get(self::PARAM_CANT_PAY_ONLINE) === "on";

		$carrier->setSurcharge($surcharge > 0 ? $surcharge : null);
		$carrier->setCommission($commission > 0 ? $commission : null);
		$carrier->setCantPayOnline($cantPayOnline);
		$this->carrierService->saveCarrier($carrier);

		return RedirectResponse::create($this->router->generate("backend_carriers"));
	}

	/**
	 * @Route(path="/backend/_ajax/carrier/{carrier}/changeName", name="backend_ajax_carrier_change_name", methods={"POST"})
	 * @param Carrier $carrier
	 * @return JsonResponse
	 */
	public function ajaxCarrierChangeNameAction(Carrier $carrier, Request $request)
	{
		$carrier->setName($request->request->get(self::PARAM_VALUE));
		$this->carrierService->saveCarrier($carrier);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/carrier/{carrier}/remove", name="backend_carrier_remove", methods={"POST"})
	 * @param Carrier $carrier
	 * @return RedirectResponse
	 */
	public function carrierRemoveAction(Carrier $carrier)
	{
		$carrier->setDeleted(true);
		$this->carrierService->saveCarrier($carrier);

		return RedirectResponse::create($this->router->generate("backend_carriers"));
	}
	
	/**
	 * @Route(path="/backend/carrier/{carrier}/restore", name="backend_carrier_restore", methods={"POST"})
	 * @param Carrier $carrier
	 * @return RedirectResponse
	 */
	public function carrierRestoreAction(Carrier $carrier)
	{
		$carrier->setDeleted(false);
		$this->carrierService->saveCarrier($carrier);

		return RedirectResponse::create($this->router->generate("backend_carriers"));
	}

}
