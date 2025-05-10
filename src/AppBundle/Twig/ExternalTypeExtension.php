<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 14:24
 */

namespace AppBundle\Twig;


use AppBundle\Entity\ExternalCity;
use AppBundle\Entity\ExternalCityBlabla;
use AppBundle\Entity\ExternalCityEastExpress;
use AppBundle\Entity\ExternalCityEcolines;
use AppBundle\Entity\ExternalCityEurolines;
use AppBundle\Entity\ExternalCityFlixbus;
use AppBundle\Entity\ExternalCityInfobus;
use AppBundle\Entity\ExternalCityLikeBus;
use AppBundle\Entity\ExternalCityNikolo;
use AppBundle\Entity\ExternalCityNikoloBusSystem;
use AppBundle\Entity\ExternalCityRegabus;
use AppBundle\Entity\ExternalCityStudentAgency;
use AppBundle\Entity\ExternalCityTransTempo;
use AppBundle\Entity\ExternalTariff;
use AppBundle\Entity\ExternalTariffEastExpress;
use AppBundle\Entity\ExternalTariffEcolines;
use AppBundle\Entity\ExternalTariffEurolines;
use AppBundle\Entity\ExternalTariffFlixbus;
use AppBundle\Entity\ExternalTariffInfobus;
use AppBundle\Entity\ExternalTariffLikeBus;
use AppBundle\Entity\ExternalTariffNikolo;
use AppBundle\Entity\ExternalTariffRegabus;
use AppBundle\Entity\ExternalTariffStudentAgency;
use Twig_SimpleTest;

class ExternalTypeExtension extends \Twig_Extension
{
	public function getTests()
	{
		return [
			new Twig_SimpleTest("externalCityStudentAgency", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityStudentAgency;
			}),
			new Twig_SimpleTest("externalCityEastExpress", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityEastExpress;
			}),
			new Twig_SimpleTest("externalCityEcolines", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityEcolines;
			}),
			new Twig_SimpleTest("externalCityFlixbus", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityFlixbus;
			}),
			new Twig_SimpleTest("externalCityInfobus", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityInfobus;
			}),
			new Twig_SimpleTest("externalCityEurolines", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityEurolines;
			}),
			new Twig_SimpleTest("externalCityNikolo", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityNikolo;
			}),
			new Twig_SimpleTest("externalCityNikoloBusSystem", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityNikoloBusSystem;
			}),
			new Twig_SimpleTest("externalCityRegabus", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityRegabus;
			}),
			new Twig_SimpleTest("externalCityBlabla", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityBlabla;
			}),
			new Twig_SimpleTest("externalCityTransTempo", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityTransTempo;
			}),
			new Twig_SimpleTest("externalCityLikeBus", function(ExternalCity $externalCity) {
				return $externalCity instanceof ExternalCityLikeBus;
			}),

			new Twig_SimpleTest("externalTariffStudentAgency", function(ExternalTariff $externalTariff) {
				return $externalTariff instanceof ExternalTariffStudentAgency;
			}),
			new Twig_SimpleTest("externalTariffInfobus", function(ExternalTariff $externalTariff) {
				return $externalTariff instanceof ExternalTariffInfobus;
			}),
			new Twig_SimpleTest("externalTariffEcolines", function(ExternalTariff $externalTariff) {
				return $externalTariff instanceof ExternalTariffEcolines;
			}),
			new Twig_SimpleTest("externalTariffEastExpress", function(ExternalTariff $externalTariff) {
				return $externalTariff instanceof ExternalTariffEastExpress;
			}),
			new Twig_SimpleTest("externalTariffFlixbus", function(ExternalTariff $externalTariff) {
				return $externalTariff instanceof ExternalTariffFlixbus;
			}),
			new Twig_SimpleTest("externalTariffEurolines", function(ExternalTariff $externalTariff) {
				return $externalTariff instanceof ExternalTariffEurolines;
			}),
			new Twig_SimpleTest("externalTariffNikolo", function(ExternalTariff $externalTariff) {
				return $externalTariff instanceof ExternalTariffNikolo;
			}),
			new Twig_SimpleTest("externalTariffRegabus", function(ExternalTariff $externalTariff) {
				return $externalTariff instanceof ExternalTariffRegabus;
			}),
			new Twig_SimpleTest("externalTariffLikeBus", function(ExternalTariff $externalTariff) {
				return $externalTariff instanceof ExternalTariffLikeBus;
			}),
		];
	}

	public function getName()
	{
		return "externalType";
	}

}
