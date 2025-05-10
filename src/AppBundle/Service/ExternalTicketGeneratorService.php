<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 29.11.17
 * Time: 22:51
 */

namespace AppBundle\Service;


use AppBundle\Entity\ExternalTicket;
use AppBundle\Entity\ExternalTicketEcolines;
use AppBundle\Entity\ExternalTicketEurolines;
use AppBundle\Entity\ExternalTicketHtmlBodyInterface;
use AppBundle\Entity\ExternalTicketStudentAgency;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExternalTicketGeneratorService
{
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var WebDriverService
	 */
	private $webDriverService;
	/**
	 * @var UploadService
	 */
	private $uploadService;
	/**
	 * @var Container
	 */
	private $container;
	/**
	 * @var TwigEngine
	 */
	private $twigEngine;
	/**
	 * @var GotenbergService
	 */
	private $gotenbergService;

	public function __construct(Router $router,
	                            WebDriverService $webDriverService,
	                            UploadService $uploadService,
	                            ContainerInterface $container,
	                            TwigEngine $twigEngine,
	                            GotenbergService $gotenbergService)
	{
		$this->router = $router;
		$this->webDriverService = $webDriverService;
		$this->uploadService = $uploadService;
		$this->container = $container;
		$this->twigEngine = $twigEngine;
		$this->gotenbergService = $gotenbergService;
	}

	/**
	 * @param ExternalTicket $externalTicket
	 * @return ExternalTicket
	 * @throws \Exception
	 */
	public function generateFile(ExternalTicket $externalTicket)
	{
		if ($externalTicket instanceof ExternalTicketEcolines) {
			$html = $this->twigEngine->render("@App/Frontend/TicketGenerator/EcolinesTicket.html.twig", [
				"externalTicket" => $externalTicket
			]);
			$file = $this->uploadService->createFile("pdf");

			$pdfContent = $this->gotenbergService->generatePdfFromHtml($html);
			file_put_contents($this->uploadService->getWebDir() . $file, $pdfContent);

			$externalTicket->setContentType("application/pdf");
			$externalTicket->setFile($file);

		} elseif ($externalTicket instanceof ExternalTicketEurolines) {
			$file = $this->uploadService->createFile("pdf");
			file_put_contents($this->uploadService->getWebDir() . $file, $externalTicket->getPdfBody());
			$externalTicket->setContentType("application/pdf");
			$externalTicket->setFile($file);

		} elseif ($externalTicket instanceof ExternalTicketStudentAgency) {
			$file = $this->uploadService->createFile("png");
			file_put_contents($this->uploadService->getWebDir() . $file, $externalTicket->getImageBody());
			$externalTicket->setContentType("image/png");
			$externalTicket->setFile($file);

		} elseif ($externalTicket instanceof ExternalTicketHtmlBodyInterface) {
			$file = $this->uploadService->createFile("pdf");
			$pdfContent = $this->gotenbergService->generatePdfFromHtml($externalTicket->getHtmlBody());
			file_put_contents($this->uploadService->getWebDir() . $file, $pdfContent);

			$externalTicket->setContentType("application/pdf");
			$externalTicket->setFile($file);

		} else {
			throw new \Exception("Define generateFile() for ticket type " . get_class($externalTicket));
		}

		return $externalTicket;
	}
}
