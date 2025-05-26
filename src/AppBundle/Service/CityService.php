<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:45
 */

namespace AppBundle\Service;


use AppBundle\Entity\City;
use AppBundle\Repository\CityRepository;

class CityService
{
	/**
	 * @var CityRepository
	 */
	private $cityRepository;

	public function __construct(CityRepository $cityRepository)
	{
		$this->cityRepository = $cityRepository;
	}

	/**
	 * @param int $id
	 * @return null|City
	 */
	public function getCity($id)
	{
		return $this->cityRepository->find($id);
	}

	/**
	 * @param City $city
	 */
	public function saveCity(City $city)
	{
		$this->cityRepository->save($city);
	}

	/**
	 * @return City[]
	 */
	public function findAllCities()
	{
		return $this->cityRepository->findBy([ "deleted" => false ]);
	}

	/**
	 * @return City|null
	 */
	public function getPragueCity()
	{
		return $this->getCity(921);
	}
}