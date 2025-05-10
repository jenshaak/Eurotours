<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:53
 */

namespace AppBundle\Repository;


use AppBundle\Entity\LineStation;
use Doctrine\ORM\EntityRepository;

class LineStationRepository extends EntityRepository
{
	public function save(LineStation $lineStation)
	{
		$this->getEntityManager()->persist($lineStation);
		$this->getEntityManager()->flush($lineStation);
	}

	public function remove(LineStation $lineStation)
	{
		$this->getEntityManager()->remove($lineStation);
		$this->getEntityManager()->flush($lineStation);
	}

    public function deleteLineStation(LineStation $lineStation)
    {
		$id = $lineStation->getId();

		$sql = "UPDATE lines_stations SET deleted = 1 WHERE line_station_id = '{$id}'";
		$this->getEntityManager()->getConnection()->prepare($sql)->execute();

		$sql = "UPDATE lines_stations SET deleted = 1 WHERE line_station_id = '{$id}'";
		$this->getEntityManager()->getConnection()->prepare($sql)->execute();

		$sql = "UPDATE schedules_lines_stations SET deleted = 1 WHERE line_station_id = '{$id}'";
		$this->getEntityManager()->getConnection()->prepare($sql)->execute();

		$sql = "UPDATE fares SET deleted = 1 WHERE from_line_station_id = '{$id}'";
		$this->getEntityManager()->getConnection()->prepare($sql)->execute();

		$sql = "UPDATE fares SET deleted = 1 WHERE to_line_station_id = '{$id}'";
		$this->getEntityManager()->getConnection()->prepare($sql)->execute();
    }
}
