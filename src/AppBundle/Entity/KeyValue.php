<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.01.17
 * Time: 11:21
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KeyValueRepository")
 * @ORM\Table(name="key_values")
 */
class KeyValue
{
	/**
	 * @var string
	 *
	 * @ORM\Column(name="id", type="string", length=32)
	 * @ORM\Id
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="value", type="text", nullable=true)
	 */
	private $value;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_updated", type="datetime", nullable=false)
	 */
	private $datetimeUpdated;

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
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value)
	{
		$this->datetimeUpdated = new \DateTime;
		$this->value = $value;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeUpdated()
	{
		return $this->datetimeUpdated;
	}

	/**
	 * @param \DateTime $datetimeUpdated
	 */
	public function setDatetimeUpdated($datetimeUpdated)
	{
		$this->datetimeUpdated = $datetimeUpdated;
	}

}