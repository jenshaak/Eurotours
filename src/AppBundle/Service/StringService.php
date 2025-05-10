<?php

namespace AppBundle\Service;

class StringService
{
	public function textToFloat(string $text): ?float
	{
		$text = trim($text);
		$text = str_replace(",", ".", $text);
		return (float) $text;
	}
}
