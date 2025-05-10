<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.05.17
 * Time: 16:59
 */

namespace AppBundle\Repository;


use AppBundle\Entity\ExternalTariff;
use AppBundle\VO\ExternalRouter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class ExternalTariffRepository extends EntityRepository
{
	public function save(ExternalTariff $externalTariff)
	{
		$this->getEntityManager()->persist($externalTariff);
		$this->getEntityManager()->flush($externalTariff);
	}

	/**
	 * @param string $ident
	 * @param string $type
	 * @return ExternalTariff|null
	 * @throws \Exception
	 */
	public function getExternalTariffByIdent($ident, $type)
	{
		$query = $this->createSearchQuery();

		$query->andWhere("et.ident = :ident")->setParameter("ident", $ident);
		$query->andWhere("et.deleted = :deleted")->setParameter("deleted", false);

		if ($type == ExternalRouter::STUDENT_AGENCY) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffStudentAgency");
		} elseif ($type == ExternalRouter::ECOLINES) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffEcolines");
		} elseif ($type == ExternalRouter::INFOBUS) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffInfobus");
		} elseif ($type == ExternalRouter::EAST_EXPRESS) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffEastExpress");
		} elseif ($type == ExternalRouter::FLIXBUS) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffFlixbus");
		} elseif ($type == ExternalRouter::EUROLINES) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffEurolines");
		} elseif ($type == ExternalRouter::NIKOLO) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffNikolo");
		} elseif ($type == ExternalRouter::REGABUS) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffRegabus");
		} elseif ($type == ExternalRouter::BLABLA) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffBlabla");
		} elseif ($type == ExternalRouter::TRANS_TEMPO) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffTransTempo");
		} elseif ($type == ExternalRouter::LIKEBUS) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffLikeBus");
		} else {
			throw new \Exception;
		}

		$query->orderBy('et.id', 'DESC');

		$externalTariffs = $query->getQuery()->getResult();

		return $externalTariffs[0] ?? null;
	}

	/**
	 * @param string $type
	 * @return \AppBundle\Entity\ExternalTariff[]
	 * @throws \Exception
	 */
	public function findExternalTariffsForType($type)
	{
		$query = $this->createSearchQuery();

		$query->andWhere("et.deleted = :deleted")->setParameter("deleted", false);

		if ($type == ExternalRouter::STUDENT_AGENCY) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffStudentAgency");
		} elseif ($type == ExternalRouter::ECOLINES) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffEcolines");
		} elseif ($type == ExternalRouter::INFOBUS) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffInfobus");
		} elseif ($type == ExternalRouter::EAST_EXPRESS) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffEastExpress");
		} elseif ($type == ExternalRouter::FLIXBUS) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffFlixbus");
		} elseif ($type == ExternalRouter::EUROLINES) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffEurolines");
		} elseif ($type == ExternalRouter::NIKOLO) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffNikolo");
		} elseif ($type == ExternalRouter::LIKEBUS) {
			$query->andWhere("et INSTANCE OF AppBundle\\Entity\\ExternalTariffLikeBus");
		} else {
			throw new \Exception;
		}

		return $query->getQuery()->getResult();
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function createSearchQuery()
	{
		$query = $this->createQueryBuilder("et");
		$query->select("et");
		return $query;
	}
}
