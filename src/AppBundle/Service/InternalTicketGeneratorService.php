<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 16.07.18
 * Time: 21:25
 */

namespace AppBundle\Service;


use AppBundle\Entity\InternalTicket;
use Knp\Snappy\Pdf;
use Symfony\Bundle\TwigBundle\TwigEngine;

class InternalTicketGeneratorService
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

	/**
	 * @param \AppBundle\Entity\InternalTicket $internalTicket
	 * @throws \Twig\Error\Error
	 */
	public function generateTicket(InternalTicket $internalTicket)
	{
		$file = $this->uploadService->createFile("pdf");
		$fileWithoutPrice = str_replace(".pdf", "_wop.pdf", $file);

		file_put_contents(
			$this->uploadService->getWebDir() . $file,
			$this->gotenbergService->generatePdfFromHtml($this->generateHtml($internalTicket))
		);
		$internalTicket->generateWithPrice = false;
		file_put_contents(
			$this->uploadService->getWebDir() . $fileWithoutPrice,
			$this->gotenbergService->generatePdfFromHtml($this->generateHtml($internalTicket))
		);

		$internalTicket->setFile($file);
	}

	/**
	 * @param InternalTicket $internalTicket
	 * @return string
	 * @throws \Twig\Error\Error
	 */
	private function generateHtml(InternalTicket $internalTicket)
	{
		return $this->twigEngine->render("@App/Frontend/TicketGenerator/internalTicket.html.twig", [
			"internalTicket" => $internalTicket
		]);
	}
}
