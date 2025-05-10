<?php


namespace AppBundle\Service;


use AppBundle\VO\SimpleInternalTicket;
use Symfony\Bundle\TwigBundle\TwigEngine;

class SimpleInternalTicketGeneratorService
{
	/**
	 * @var TwigEngine
	 */
	private $twigEngine;
	/**
	 * @var UploadService
	 */
	private $uploadService;
	/**
	 * @var GotenbergService
	 */
	private $gotenbergService;

	public function __construct(TwigEngine $twigEngine,
								UploadService $uploadService,
								GotenbergService $gotenbergService)
	{
		$this->twigEngine = $twigEngine;
		$this->uploadService = $uploadService;
		$this->gotenbergService = $gotenbergService;
	}

	public function generateTicket(SimpleInternalTicket $ticket)
	{
		$html = $this->generateHtml($ticket);
		$file = $this->uploadService->createFile("pdf");

		$pdfContent = $this->gotenbergService->generatePdfFromHtml($html);
		file_put_contents($this->uploadService->getWebDir() . $file, $pdfContent);

		$ticket->setFile($file);
	}

	private function generateHtml(SimpleInternalTicket $ticket)
	{
		return $this->twigEngine->render("@App/Frontend/TicketGenerator/simpleInternalTicket.html.twig", [
			"ticket" => $ticket
		]);
	}
}
