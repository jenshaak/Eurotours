<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.08.17
 * Time: 16:12
 */

namespace AppBundle\VO;


use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class CurrencyBag extends AttributeBag
{
	const KEY_CURRENCY = "currency";

	/** @var string */
	private $currency;

	public function initialize(array &$attributes)
	{
		parent::initialize($attributes);

		if ($this->has(self::KEY_CURRENCY)) {
			$this->currency = $this->get(self::KEY_CURRENCY);
		}
	}

	public function getName()
	{
		return get_class($this);
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
		$this->set(self::KEY_CURRENCY, $this->currency = $currency);

		return $this;
	}
}