<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 12:03
 */

namespace AppBundle\Repository;


use AppBundle\Entity\ExternalStation;
use AppBundle\VO\ExternalRouter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class ExternalStationRepository extends EntityRepository
{
	public function save(ExternalStation $externalStation)
	{
		$this->getEntityManager()->persist($externalStation);
		$this->getEntityManager()->flush();
	}

	/**
	 * @param $ident
	 * @param $type
	 * @return ExternalStation|null
	 * @throws \Exception
	 */
	public function getExternalStationByIdent($ident, $type)
	{
		$query = $this->createSearchQuery();

		$query->andWhere("es.ident = :ident")->setParameter("ident", $ident);

		if ($type == ExternalRouter::STUDENT_AGENCY) {
			$query->andWhere("es INSTANCE OF AppBundle\\Entity\\ExternalStationStudentAgency");
		} elseif ($type == ExternalRouter::FLIXBUS) {
			$query->andWhere("es INSTANCE OF AppBundle\\Entity\\ExternalStationFlixbus");
		} elseif ($type == ExternalRouter::EUROLINES) {
			$query->andWhere("es INSTANCE OF AppBundle\\Entity\\ExternalStationEurolines");
		} elseif ($type == ExternalRouter::INFOBUS) {
			$query->andWhere("es INSTANCE OF AppBundle\\Entity\\ExternalStationInfobus");
		} elseif ($type == ExternalRouter::NIKOLO) {
			$query->andWhere("es INSTANCE OF AppBundle\\Entity\\ExternalStationNikolo");
		} elseif ($type == ExternalRouter::REGABUS) {
			$query->andWhere("es INSTANCE OF AppBundle\\Entity\\ExternalStationRegabus");
		} elseif ($type == ExternalRouter::BLABLA) {
			$query->andWhere("es INSTANCE OF AppBundle\\Entity\\ExternalStationBlabla");
		} elseif ($type == ExternalRouter::LIKEBUS) {
			$query->andWhere("es INSTANCE OF AppBundle\\Entity\\ExternalStationLikeBus");
		} else {
			throw new \Exception;
		}

		try {
			return $query->getQuery()->getSingleResult();
		} catch (NoResultException $e) {
			return null;
		}
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function createSearchQuery()
	{
		$query = $this->createQueryBuilder("es");
		$query->select("es");
		return $query;
	}
}
