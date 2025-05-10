<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 27.11.17
 * Time: 17:00
 */

namespace AppBundle\VO;


use Doctrine\Common\Collections\ArrayCollection;

class DuplicatorVO
{
	/** @var array|\stdClass[] */
	private $data = [];

	/**
	 * @param object $object
	 * @return object|mixed
	 */
	public function duplicate($object)
	{
		if ($object === null) return null;

		foreach ($this->data as $d) {
			if ($d->old === $object) return $d->new;
		}

		$d = (object) [
			"old" => $object,
			"new" => $new = clone $object
		];

		$this->data[] = $d;

		return $d->new;
	}

	/**
	 * @param ArrayCollection $arrayCollection
	 * @return ArrayCollection
	 */
	public function processArrayCollection($arrayCollection)
	{
		$return = new ArrayCollection;

		$arrayCollection->map(function ($object) use ($return) {
			$return->add($this->duplicate($object));
		});

		return $return;
	}
}