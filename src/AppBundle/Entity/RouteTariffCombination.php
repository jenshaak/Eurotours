<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 10.07.17
 * Time: 16:56
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RouteTariffCombinationRepository")
 * @ORM\Table(name="routes_tariffs_combinations")
 */
class RouteTariffCombination
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="route_tariff_combination_id", type="string", length=64)
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	private $id;

	/**
	 * @var RouteCombination
	 * @ORM\ManyToOne(targetEntity="RouteCombination", inversedBy="routeTariffCombinations")
	 * @ORM\JoinColumn(name="route_combination_id", referencedColumnName="route_combination_id", nullable=false)
	 */
	private $routeCombination;

	/**
	 * @var RouteTariff
	 * @ORM\ManyToOne(targetEntity="RouteTariff")
	 * @ORM\JoinColumn(name="there_route_tariff_id", referencedColumnName="route_tariff_id", nullable=true)
	 */
	private $thereRouteTariff;

	/**
	 * @var RouteTariff
	 * @ORM\ManyToOne(targetEntity="RouteTariff")
	 * @ORM\JoinColumn(name="back_route_tariff_id", referencedColumnName="route_tariff_id", nullable=true)
	 */
	private $backRouteTariff;

	/**
	 * @var int
	 * @ORM\Column(name="price", type="integer", nullable=true)
	 */
	private $price;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return RouteTariff
	 */
	public function getThereRouteTariff()
	{
		return $this->thereRouteTariff;
	}

	/**
	 * @param RouteTariff $thereRouteTariff
	 */
	public function setThereRouteTariff($thereRouteTariff)
	{
		$this->thereRouteTariff = $thereRouteTariff;
	}

	/**
	 * @return RouteTariff
	 */
	public function getBackRouteTariff()
	{
		return $this->backRouteTariff;
	}

	/**
	 * @param RouteTariff $backRouteTariff
	 */
	public function setBackRouteTariff($backRouteTariff)
	{
		$this->backRouteTariff = $backRouteTariff;
	}

	/**
	 * @return int
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param int $price
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @return RouteCombination
	 */
	public function getRouteCombination()
	{
		return $this->routeCombination;
	}

	/**
	 * @param RouteCombination $routeCombination
	 */
	public function setRouteCombination($routeCombination)
	{
		$this->routeCombination = $routeCombination;
	}

}