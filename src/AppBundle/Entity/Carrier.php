<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 12:07
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CarrierRepository")
 * @ORM\Table(name="carriers")
 */
class Carrier
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="carrier_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="code", type="string", length=16, nullable=false, unique=true)
	 */
	private $code;

	/**
	 * @var string
	 * @ORM\Column(name="name", type="string", length=64, nullable=false)
	 */
	private $name;

	/**
	 * @var Line[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="Line", mappedBy="carrier")
	 */
	private $lines;

	/**
	 * @var boolean
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	/**
	 * @var int
	 * @ORM\Column(name="surcharge", type="integer", nullable=true)
	 */
	private $surcharge;

	/**
	 * @var int
	 * @ORM\Column(name="commission", type="integer", nullable=true)
	 */
	private $commission;

	/**
	 * @var boolean
	 * @ORM\Column(name="cant_pay_online", type="boolean", nullable=false)
	 */
	private $cantPayOnline = false;

	/**
	 * @var boolean
	 * @ORM\Column(name="external_search")
	 */
	private $externalSearch = false;

	/**
	 * @var boolean
	 * @ORM\Column(name="external_buy")
	 */
	private $externalBuy = false;

	public function __construct()
	{
		$this->lines = new ArrayCollection;
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
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return Line[]|ArrayCollection
	 */
	public function getLines()
	{
		return $this->lines->filter(function (Line $line) { return !$line->isDeleted(); });
	}

	/**
	 * @return bool
	 */
	public function isDeleted()
	{
		return $this->deleted;
	}

	/**
	 * @param bool $deleted
	 */
	public function setDeleted($deleted)
	{
		$this->deleted = $deleted;
	}

	/**
	 * @return int
	 */
	public function getSurcharge()
	{
		return $this->surcharge;
	}

	/**
	 * @param int $surcharge
	 */
	public function setSurcharge($surcharge)
	{
		$this->surcharge = $surcharge;
	}

	/**
	 * @return int
	 */
	public function getCommission()
	{
		return $this->commission;
	}

	/**
	 * @param int $commission
	 */
	public function setCommission($commission)
	{
		$this->commission = $commission;
	}

	/**
	 * @return bool
	 */
	public function isCantPayOnline()
	{
		return $this->cantPayOnline;
	}

	/**
	 * @param bool $cantPayOnline
	 */
	public function setCantPayOnline($cantPayOnline)
	{
		$this->cantPayOnline = $cantPayOnline;
	}

	/**
	 * @return bool
	 */
	public function isExternalSearch()
	{
		return $this->externalSearch;
	}

	/**
	 * @param bool $externalSearch
	 */
	public function setExternalSearch($externalSearch)
	{
		$this->externalSearch = $externalSearch;
	}

	/**
	 * @return bool
	 */
	public function isExternalBuy()
	{
		return $this->externalBuy;
	}

	/**
	 * @param bool $externalBuy
	 */
	public function setExternalBuy($externalBuy)
	{
		$this->externalBuy = $externalBuy;
	}

}
