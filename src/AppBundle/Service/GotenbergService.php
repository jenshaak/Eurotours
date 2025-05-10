<?php


namespace AppBundle\Service;


use TheCodingMachine\Gotenberg\Client;
use TheCodingMachine\Gotenberg\DocumentFactory;
use TheCodingMachine\Gotenberg\HTMLRequest;
use TheCodingMachine\Gotenberg\Request;

class GotenbergService
{
	/**
	 * @var string
	 */
	private $gotenbergUrl;

	public function __construct(string $gotenbergUrl)
	{
		$this->gotenbergUrl = $gotenbergUrl;
	}

	public function generatePdfFromHtml(string $html): string
	{
		$gotenberg = new Client($this->gotenbergUrl);
		$document = DocumentFactory::makeFromString(uniqid() . ".html", $html);
		$request = new HTMLRequest($document);
		$request->setPaperSize(Request::A4);
		$request->setMargins([ 0.35, 0.35, 0.35, 0.35 ]);
		$tmpFile = tempnam(sys_get_temp_dir(), "gotenberg") . ".pdf";
		$gotenberg->store($request, $tmpFile);
		$return = file_get_contents($tmpFile);
		unlink($tmpFile);
		return $return;
	}
}
