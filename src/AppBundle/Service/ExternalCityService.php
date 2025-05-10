<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 12:15
 */

namespace AppBundle\Service;


use AppBundle\Entity\Carrier;
use AppBundle\Entity\ExternalCity;
use AppBundle\Repository\ExternalCityRepository;

class ExternalCityService
{
	/**
	 * @var ExternalCityRepository
	 */
	private $externalCityRepository;

	public function __construct(ExternalCityRepository $externalCityRepository)
	{
		$this->externalCityRepository = $externalCityRepository;
	}

	/**
	 * @param ExternalCity $externalCity
	 */
	public function saveExternalCity(ExternalCity $externalCity)
	{
		$this->externalCityRepository->save($externalCity);
	}

	/**
	 * @param string $ident
	 * @param string $type
	 * @return ExternalCity|null
	 * @throws \Exception
	 */
	public function getExternalCityByIdent($ident, $type)
	{
		return $this->externalCityRepository->getExternalCityByIdent($ident, $type);
	}

	/**
	 * @return ExternalCity[]
	 */
	public function findAllExternalCities()
	{
		return $this->externalCityRepository->findAll();
	}

	/**
	 * @param $type
	 * @return ExternalCity[]
	 * @throws \Exception
	 */
	public function findExternalCitiesByType($type)
	{
		return $this->externalCityRepository->getExternalCitiesByType($type);
	}

	/**
	 * @return ExternalCity[]
	 */
	public function findWaitingExternalCities()
	{
		return $this->externalCityRepository->findBy([ "processed" => false ]);
	}

	/**
	 * @param int $id
	 * @return null|ExternalCity
	 */
	public function getExternalCity($id)
	{
		return $this->externalCityRepository->find($id);
	}
}