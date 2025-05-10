<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.04.17
 * Time: 0:31
 */

namespace AppBundle\Service;


use AppBundle\Entity\Fare;
use AppBundle\Entity\Tariff;
use AppBundle\Repository\TariffRepository;
use AppBundle\VO\DuplicatorVO;

class TariffService
{
	/**
	 * @var TariffRepository
	 */
	private $tariffRepository;
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(TariffRepository $tariffRepository,
								LanguageService $languageService)
	{
		$this->tariffRepository = $tariffRepository;
		$this->languageService = $languageService;
	}

	/**
	 * @param int $id
	 * @return null|Tariff
	 */
	public function getTariff($id)
	{
		/** @var Tariff|null $tariff */
		$tariff = $this->tariffRepository->find($id);
		return $tariff;
	}

	/**
	 * @param string $table
	 * @return null|Tariff
	 */
	public function getTariffByOldPriceTable($table)
	{
		/** @var Tariff|null $tariff */
		$tariff = $this->tariffRepository->findOneBy([ "oldPriceTable" => $table ]);
		return $tariff;
	}

	/**
	 * @param Tariff $tariff
	 */
	public function saveTariff(Tariff $tariff)
	{
		$this->tariffRepository->save($tariff);
	}

	/**
	 * @return Tariff[]
	 */
	public function findAllTariffs()
	{
		return $this->tariffRepository->findAll();
	}

    public function duplicateTariff(Tariff $oldTariff)
    {
		$duplicator = new DuplicatorVO;
		/** @var Tariff $tariff */
		$tariff = $duplicator->duplicate($oldTariff);
		foreach ($this->languageService->getAllLanguages() as $language) {
			$tariff->setName($language, $tariff->getName()->getString($language) . " copy");
		}
		$tariff->setFares($duplicator->processArrayCollection($tariff->getFares()));
		$tariff->getFares()->map(function (Fare $fare) use ($duplicator) {
			$fare->setTariff($duplicator->duplicate($fare->getTariff()));
		});
		$tariff->setOtherCurrencyTariff(null);

		return $tariff;
    }
}
