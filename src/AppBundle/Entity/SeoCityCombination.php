<?php


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SeoCityCombinationRepository")
 * @ORM\Table(name="seo_cities_combinations")
 */
class SeoCityCombination
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="seo_city_combination", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var City
	 * @ORM\ManyToOne(targetEntity="City")
	 * @ORM\JoinColumn(name="from_city_id", referencedColumnName="city_id", nullable=false)
	 */
	private $fromCity;

	/**
	 * @var City
	 * @ORM\ManyToOne(targetEntity="City")
	 * @ORM\JoinColumn(name="to_city_id", referencedColumnName="city_id", nullable=false)
	 */
	private $toCity;

	/**
	 * @var array|string[]
	 * @ORM\Column(name="routes", type="simple_array", nullable=false)
	 */
	private $routes = [];

	/**
	 * @var string
	 * @ORM\Column(name="from_slug", type="string", nullable=false)
	 */
	private $fromSlug;

	/**
	 * @var string
	 * @ORM\Column(name="to_slug", type="string", nullable=false)
	 */
	private $toSlug;

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
	 * @return City
	 */
	public function getFromCity()
	{
		return $this->fromCity;
	}

	/**
	 * @param City $fromCity
	 */
	public function setFromCity($fromCity)
	{
		$this->fromCity = $fromCity;
	}

	/**
	 * @return City
	 */
	public function getToCity()
	{
		return $this->toCity;
	}

	/**
	 * @param City $toCity
	 */
	public function setToCity($toCity)
	{
		$this->toCity = $toCity;
	}

	/**
	 * @return array|string[]
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * @param array|string[] $routes
	 */
	public function setRoutes($routes)
	{
		$this->routes = $routes;
	}

	/**
	 * @return string
	 */
	public function getFromSlug()
	{
		return $this->fromSlug;
	}

	/**
	 * @param string $fromSlug
	 */
	public function setFromSlug($fromSlug)
	{
		$this->fromSlug = $fromSlug;
	}

	/**
	 * @return string
	 */
	public function getToSlug()
	{
		return $this->toSlug;
	}

	/**
	 * @param string $toSlug
	 */
	public function setToSlug($toSlug)
	{
		$this->toSlug = $toSlug;
	}

}
