<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.05.17
 * Time: 16:29
 */

namespace AppBundle\Entity;
use AppBundle\VO\LanguageString;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ExternalTariffRepository")
 * @ORM\Table(name="external_tariffs")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap(
 *     {
 *         "student_agency" = "ExternalTariffStudentAgency",
 *         "ecolines" = "ExternalTariffEcolines",
 *         "infobus" = "ExternalTariffInfobus",
 *         "east_express" = "ExternalTariffEastExpress",
 *         "flixbus" = "ExternalTariffFlixbus",
 *         "eurolines" = "ExternalTariffEurolines",
 *         "nikolo" = "ExternalTariffNikolo",
 *         "regabus" = "ExternalTariffRegabus",
 *         "blabla" = "ExternalTariffBlabla",
 *         "trans_tempo" = "ExternalTariffTransTempo",
 *         "likebus" = "ExternalTariffLikeBus"
 *     }
 * )
 */
abstract class ExternalTariff
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="external_tariff_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="ident", type="string", length=64, nullable=false)
	 */
	private $ident;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="name", type="object", nullable=false)
	 */
	private $name;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="conditions", type="object", nullable=false)
	 */
	private $conditions;

	/**
	 * @var boolean
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	public function __construct()
	{
		$this->name = new LanguageString;
		$this->conditions = new LanguageString;
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
	public function getIdent()
	{
		return $this->ident;
	}

	/**
	 * @param string $ident
	 */
	public function setIdent($ident)
	{
		$this->ident = $ident;
	}

	/**
	 * @return LanguageString
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param Language $language
	 * @param string $name
	 */
	public function setName(Language $language, $name)
	{
		$this->name = clone $this->getName()->setString($language, $name);
	}

	/**
	 * @return LanguageString
	 */
	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * @param Language $language
	 * @param string $conditions
	 */
	public function setConditions(Language $language, $conditions)
	{
		$this->conditions = clone $this->getConditions()->setString($language, $conditions);
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

}
