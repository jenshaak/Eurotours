<?php


namespace AppBundle\Twig;


class NonBreakingSpaceExtension extends \Twig_Extension
{
	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter("nonBreakingSpace", [ $this, "nonBreakingSpace" ], [ "is_safe" => [ "html" ]])
		];
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function nonBreakingSpace($text)
	{
		return str_replace(" ", "&nbsp;", $text);
	}

	public function getName()
	{
		return "nonBreakingSpace";
	}

}
