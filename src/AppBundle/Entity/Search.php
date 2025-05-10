<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 11:30
 */

namespace AppBundle\Entity;

use AppBundle\VO\RouteFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SearchRepository")
 * @ORM\Table(name="searches")
 */
class Search
{
	/**
	 * @var string
	 * @ORM\Id()
	 * @ORM\Column(name="search_id", type="string", length=255)
	 * @ORM\GeneratedValue(strategy="UUID")
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
	 * @var \DateTime
	 * @ORM\Column(name="date_day", type="date", nullable=false)
	 */
	private $dateDay;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="date_back", type="date", nullable=true)
	 */
	private $dateBack;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_created", type="datetime", nullable=false)
	 */
	private $datetimeCreated;

	/**
	 * @var string
	 * @ORM\Column(name="type", type="string", length=64, nullable=false)
	 */
	private $type;

	/**
	 * @var SearchExternal[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="AppBundle\Entity\SearchExternal", mappedBy="search")
	 */
	private $searchExternals;

	public function __construct()
	{
		$this->datetimeCreated = new \DateTime;
		$this->searchExternals = new ArrayCollection;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $id
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
	 * @return \DateTime
	 */
	public function getDateDay()
	{
		return $this->dateDay;
	}

	/**
	 * @param \DateTime $dateDay
	 */
	public function setDateDay($dateDay)
	{
		$this->dateDay = $dateDay;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateBack()
	{
		return $this->dateBack;
	}

	/**
	 * @param \DateTime $dateBack
	 */
	public function setDateBack($dateBack)
	{
		$this->dateBack = $dateBack;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return SearchExternal[]|ArrayCollection
	 */
	public function getSearchExternals()
	{
		return $this->searchExternals;
	}

	/**
	 * @return RouteFilter
	 */
	public function createRouteFilter()
	{
		$filter = new RouteFilter;
		$filter->setFromCity($this->getFromCity());
		$filter->setToCity($this->getToCity());
		$filter->setDateDay($this->getDateDay());
		$filter->setDateBack($this->getDateBack());
		$filter->setType($this->getType());
		return $filter;
	}

}
