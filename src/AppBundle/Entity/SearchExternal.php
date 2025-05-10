<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 16:46
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SearchExternalRepository")
 * @ORM\Table(name="searches_external")
 */
class SearchExternal
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="search_external_id", type="string", length=64)
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="type", type="string", length=64, nullable=false)
	 */
	private $type;

	/**
	 * @var Search
	 * @ORM\ManyToOne(targetEntity="Search")
	 * @ORM\JoinColumn(name="search_id", referencedColumnName="search_id", nullable=false)
	 */
	private $search;

	/**
	 * @var boolean
	 * @ORM\Column(name="processed", type="boolean", nullable=false)
	 */
	private $processed = false;

	/**
	 * @var boolean
	 * @ORM\Column(name="showed", type="boolean", nullable=false)
	 */
	private $showed = false;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_created", type="datetime", nullable=false)
	 */
	private $datetimeCreated;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_processed", type="datetime", nullable=true)
	 */
	private $datetimeProcessed;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_showed", type="datetime", nullable=true)
	 */
	private $datetimeShowed;

	/**
	 * @var string
	 * @ORM\Column(name="currency", type="string", length=3, nullable=false)
	 */
	private $currency;

	/**
	 * @var bool
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	public function __construct()
	{
		$this->datetimeCreated = new \DateTime;
	}

	/**
	 * @param Search $search
	 * @param string $type
	 * @param string $currency
	 * @return SearchExternal
	 */
	public static function create(Search $search, $type, $currency)
	{
		$external = new SearchExternal;
		$external->setSearch($search);
		$external->setType($type);
		$external->setCurrency($currency);
		return $external;
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
	 * @return Search
	 */
	public function getSearch()
	{
		return $this->search;
	}

	/**
	 * @param Search $search
	 */
	public function setSearch($search)
	{
		$this->search = $search;
	}

	/**
	 * @return boolean
	 */
	public function isProcessed()
	{
		return $this->processed;
	}

	/**
	 * @param boolean $processed
	 */
	public function setProcessed($processed)
	{
		$this->processed = $processed;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeCreated()
	{
		return $this->datetimeCreated;
	}

	/**
	 * @param \DateTime $datetimeCreated
	 */
	public function setDatetimeCreated($datetimeCreated)
	{
		$this->datetimeCreated = $datetimeCreated;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeProcessed()
	{
		return $this->datetimeProcessed;
	}

	/**
	 * @param \DateTime $datetimeProcessed
	 */
	public function setDatetimeProcessed($datetimeProcessed)
	{
		$this->datetimeProcessed = $datetimeProcessed;
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
	 * @return boolean
	 */
	public function isShowed()
	{
		return $this->showed;
	}

	/**
	 * @param boolean $showed
	 */
	public function setShowed($showed)
	{
		$this->showed = $showed;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeShowed()
	{
		return $this->datetimeShowed;
	}

	/**
	 * @param \DateTime $datetimeShowed
	 */
	public function setDatetimeShowed($datetimeShowed)
	{
		$this->datetimeShowed = $datetimeShowed;
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
	}

	/**
	 * @return bool
	 */
	public function isDeleted(): bool
	{
		return $this->deleted;
	}

	/**
	 * @param bool $deleted
	 */
	public function setDeleted(bool $deleted): void
	{
		$this->deleted = $deleted;
	}
}
