<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.01.17
 * Time: 11:30
 */

namespace AppBundle\Service;


use AppBundle\Entity\KeyValue;
use AppBundle\Repository\KeyValueRepository;

class KeyValueService
{
	/**
	 * @var KeyValueRepository
	 */
	private $keyValueRepository;

	public function __construct(KeyValueRepository $keyValueRepository)
	{
		$this->keyValueRepository = $keyValueRepository;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value)
	{
		/** @var KeyValue $keyValue */
		if ($keyValue = $this->keyValueRepository->find($key)) {

		} else {
			$keyValue = new KeyValue();
			$keyValue->setId($key);
		}

		$keyValue->setValue($value);

		$this->keyValueRepository->save($keyValue);
	}

	/**
	 * @param string $key
	 * @return KeyValue
	 */
	public function get($key)
	{
		/** @var KeyValue $keyValue */
		$keyValue = $this->keyValueRepository->find($key);

		if ($keyValue == null) {
			$keyValue = new KeyValue;
			$keyValue->setId($key);
		}

		return $keyValue;
	}
}