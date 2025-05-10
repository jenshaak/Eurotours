<?php


namespace AppBundle\Twig;


use AppBundle\Entity\BookInternal;

class BookTypeExtension extends \Twig_Extension
{
	public function getTests()
	{
		return [
			new \Twig_SimpleTest("bookInternal", function ($book) { return $book instanceof BookInternal; }),
		];
	}

	public function getName()
	{
		return "bookType";
	}

}
