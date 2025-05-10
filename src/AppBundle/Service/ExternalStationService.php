<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 12:37
 */

namespace AppBundle\Service;


use AppBundle\Entity\ExternalStation;
use AppBundle\Repository\ExternalStationRepository;

class ExternalStationService
{
	/**
	 * @var ExternalStationRepository
	 */
	private $externalStationRepository;

	public function __construct(ExternalStationRepository $externalStationRepository)
	{
		$this->externalStationRepository = $externalStationRepository;
	}

	/**
	 * @param string $ident
	 * @param string $type
	 * @return ExternalStation|null
	 */
	public function getExternalStationByIdent($ident, $type)
	{
		return $this->externalStationRepository->getExternalStationByIdent($ident, $type);
	}

	public function saveExternalStation(ExternalStation $externalStation)
	{
		$this->externalStationRepository->save($externalStation);
	}

}
