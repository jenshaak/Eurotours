<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 10.07.17
 * Time: 16:52
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RouteCombinationRepository")
 * @ORM\Table(name="routes_combinations")
 */
class RouteCombination
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="route_combination_id", type="string", length=64)
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	private $id;

	/**
	 * @var Route
	 * @ORM\ManyToOne(targetEntity="Route")
	 * @ORM\JoinColumn(name="there_route_id", referencedColumnName="route_id", nullable=true)
	 */
	private $thereRoute;

	/**
	 * @var Route
	 * @ORM\ManyToOne(targetEntity="Route")
	 * @ORM\JoinColumn(name="back_route_id", referencedColumnName="route_id", nullable=true)
	 */
	private $backRoute;

	/**
	 * @var RouteTariffCombination[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="RouteTariffCombination", mappedBy="routeCombination", cascade={"persist"})
	 */
	private $routeTariffCombinations;

	public function __construct()
	{
		$this->routeTariffCombinations = new ArrayCollection;
	}

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
	 * @return Route
	 */
	public function getThereRoute()
	{
		return $this->thereRoute;
	}

	/**
	 * @param Route $thereRoute
	 */
	public function setThereRoute($thereRoute)
	{
		$this->thereRoute = $thereRoute;
	}

	/**
	 * @return Route
	 */
	public function getBackRoute()
	{
		return $this->backRoute;
	}

	/**
	 * @param Route $backRoute
	 */
	public function setBackRoute($backRoute)
	{
		$this->backRoute = $backRoute;
	}

	/**
	 * @return RouteTariffCombination[]|ArrayCollection
	 */
	public function getRouteTariffCombinations()
	{
		return $this->routeTariffCombinations;
	}

	/**
	 * @param RouteTariffCombination[]|ArrayCollection $routeTariffCombinations
	 */
	public function setRouteTariffCombinations($routeTariffCombinations)
	{
		$this->routeTariffCombinations = $routeTariffCombinations;
	}

}