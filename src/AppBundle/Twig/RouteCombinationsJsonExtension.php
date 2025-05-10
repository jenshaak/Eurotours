<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 10.07.17
 * Time: 17:52
 */

namespace AppBundle\Twig;


use AppBundle\Entity\RouteCombination;
use AppBundle\Entity\RouteTariffCombination;
use Doctrine\Common\Collections\ArrayCollection;

class RouteCombinationsJsonExtension extends \Twig_Extension
{
	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter("routeCombinationsJson", [ $this, "routeCombinationsJson" ], [ "is_safe" => [ "html" ]])
		];
	}

	public function getName()
	{
		return "routeCombinationsJson";
	}

	/**
	 * @param RouteCombination[]|ArrayCollection $routeCombinations
	 * @return string
	 */
	public function routeCombinationsJson($routeCombinations)
	{
		$array = [];

		foreach ($routeCombinations->toArray() as $routeCombination) {
			/** @var RouteCombination $routeCombination */

			foreach ($routeCombination->getRouteTariffCombinations() as $routeTariffCombination) {
				/** @var RouteTariffCombination $routeTariffCombination */

				$array[] = [
					"there" => $routeTariffCombination->getThereRouteTariff()->getId(),
					"back" => $routeTariffCombination->getBackRouteTariff()->getId(),
					"price" => $routeTariffCombination->getPrice()
				];
			}
		}

		return json_encode($array);
	}
}