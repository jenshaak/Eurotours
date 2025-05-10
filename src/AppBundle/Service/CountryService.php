<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:46
 */

namespace AppBundle\Service;


use AppBundle\Entity\Country;
use AppBundle\Repository\CountryRepository;

class CountryService
{
	/**
	 * @var CountryRepository
	 */
	private $countryRepository;

	public function __construct(CountryRepository $countryRepository)
	{
		$this->countryRepository = $countryRepository;
	}

	/**
	 * @param int $id
	 * @return null|Country
	 */
	public function getCountry($id)
	{
		return $this->countryRepository->find($id);
	}

	public function saveCountry(Country $country)
	{
		$this->countryRepository->save($country);
	}

	/**
	 * @return Country[]
	 */
	public function findAllCountries()
	{
		return $this->countryRepository->findAll();
	}
}