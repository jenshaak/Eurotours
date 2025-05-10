<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:45
 */

namespace AppBundle\Service;


use AppBundle\Entity\Carrier;
use AppBundle\Repository\CarrierRepository;

class CarrierService
{
	/**
	 * @var CarrierRepository
	 */
	private $carrierRepository;

	public function __construct(CarrierRepository $carrierRepository)
	{
		$this->carrierRepository = $carrierRepository;
	}

	/**
	 * @param int $id
	 * @return null|Carrier
	 */
	public function getCarrier($id)
	{
		/** @var Carrier|null $carrier */
		$carrier = $this->carrierRepository->find($id);
		return $carrier;
	}

	public function saveCarrier(Carrier $carrier)
	{
		$this->carrierRepository->save($carrier);
	}

	/**
	 * @return Carrier[]
	 */
	public function findAllCarriers()
	{
		return $this->carrierRepository->findBy([ "deleted" => false ], [ "name" => "ASC" ]);
	}
	
	/**
	 * @return Carrier[]
	 */
	public function findAllDeletedCarriers()
	{
		return $this->carrierRepository->findAllDeleted();
	}

	/**
	 * @param string $code
	 * @return null|Carrier
	 */
	public function getCarrierByCode($code)
	{
		/** @var Carrier|null $carrier */
		$carrier = $this->carrierRepository->findOneBy([ "code" => $code ]);
		return $carrier;
	}
	
	/**
	 * @param string $code
	 * @return bool
	 */
	public function existsCarrierWithCode($code)
	{
		return $this->carrierRepository->count(['code' => $code]) > 0;
	}

}
