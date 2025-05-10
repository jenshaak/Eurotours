<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.05.17
 * Time: 17:07
 */

namespace AppBundle\Service;


use AppBundle\Entity\ExternalTariff;
use AppBundle\Repository\ExternalTariffRepository;

class ExternalTariffService
{
	/**
	 * @var ExternalTariffRepository
	 */
	private $externalTariffRepository;

	public function __construct(ExternalTariffRepository $externalTariffRepository)
	{
		$this->externalTariffRepository = $externalTariffRepository;
	}

	/**
	 * @param string $ident
	 * @param string $type
	 * @return ExternalTariff|null
	 */
	public function getExternalTariffByIdent($ident, $type)
	{
		return $this->externalTariffRepository->getExternalTariffByIdent($ident, $type);
	}

	/**
	 * @param string $type
	 * @return \AppBundle\Entity\ExternalTariff[]
	 * @throws \Exception
	 */
	public function findExternalTariffsForType($type)
	{
		return $this->externalTariffRepository->findExternalTariffsForType($type);
	}

	/**
	 * @param int $id
	 * @return ExternalTariff|null
	 * @throws \Exception
	 */
	public function getExternalTariffById($id)
	{
		return $this->externalTariffRepository->find($id);
	}

	/**
	 * @param ExternalTariff $externalTariff
	 */
	public function saveExternalTariff(ExternalTariff $externalTariff)
	{
		$this->externalTariffRepository->save($externalTariff);
	}

	/**
	 * @return ExternalTariff[]
	 */
	public function findAllExternalTariffs()
	{
		return $this->externalTariffRepository->findBy([ "deleted" => false ]);
	}
}
