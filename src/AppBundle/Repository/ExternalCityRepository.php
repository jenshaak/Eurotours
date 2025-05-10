<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 11:52
 */

namespace AppBundle\Repository;


use AppBundle\Entity\ExternalCity;
use AppBundle\VO\ExternalRouter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class ExternalCityRepository extends EntityRepository
{
	public function save(ExternalCity $externalCity)
	{
		$this->getEntityManager()->persist($externalCity);
		$this->getEntityManager()->flush();
	}

	/**
	 * @param QueryBuilder $query
	 * @param string $type
	 * @throws \Exception
	 */
	private function queryWhereByType(QueryBuilder $query, $type)
	{
		if ($type == ExternalRouter::STUDENT_AGENCY) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityStudentAgency");
		} elseif ($type == ExternalRouter::ECOLINES) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityEcolines");
		} elseif ($type == ExternalRouter::INFOBUS) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityInfobus");
		} elseif ($type == ExternalRouter::EAST_EXPRESS) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityEastExpress");
		} elseif ($type == ExternalRouter::FLIXBUS) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityFlixbus");
		} elseif ($type == ExternalRouter::EUROLINES) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityEurolines");
		} elseif ($type == ExternalRouter::NIKOLO) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityNikoloBusSystem");
		} elseif ($type == ExternalRouter::REGABUS) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityRegabus");
		} elseif ($type == ExternalRouter::BLABLA) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityBlabla");
		} elseif ($type == ExternalRouter::TRANS_TEMPO) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityTransTempo");
		} elseif ($type == ExternalRouter::LIKEBUS) {
			$query->andWhere("ec INSTANCE OF AppBundle\\Entity\\ExternalCityLikeBus");
		} else {
			throw new \Exception;
		}
	}

	/**
	 * @param $ident
	 * @param $type
	 * @return ExternalCity|null
	 * @throws \Exception
	 */
	public function getExternalCityByIdent($ident, $type)
	{
		$query = $this->createSearchQuery();

		$query->andWhere("ec.ident = :ident")->setParameter("ident", $ident);

		$this->queryWhereByType($query, $type);

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
		$query = $this->createQueryBuilder("ec");
		$query->select("ec");
		return $query;
	}

	/**
	 * @param $type
	 * @return ExternalCity[]
	 * @throws \Exception
	 */
	public function getExternalCitiesByType($type)
	{
		$query = $this->createSearchQuery();

		$this->queryWhereByType($query, $type);

		return $query->getQuery()->getResult();
	}

}
