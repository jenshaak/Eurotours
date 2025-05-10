<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 17.07.17
 * Time: 11:47
 */

namespace AppBundle\Service;


use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\Route;
use AppBundle\Twig\PriceCurrencyExtension;
use AppBundle\VO\SimpleInternalTicket;
use tFPDF\PDF;

/**
 * @deprecated
 */
class TicketGeneratorService
{
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var PriceCurrencyExtension
	 */
	private $priceCurrencyExtension;
	/**
	 * @var UploadService
	 */
	private $uploadService;

	public function __construct(LanguageService $languageService,
	                            PriceCurrencyExtension $priceCurrencyExtension,
	                            UploadService $uploadService)
	{
		$this->languageService = $languageService;
		$this->priceCurrencyExtension = $priceCurrencyExtension;
		$this->uploadService = $uploadService;
	}

	/**
	 * @param OrderPerson $orderPerson
	 * @return PDF
	 */
	public function generateTicket(\AppBundle\Entity\InternalTicket $internalTicket)
	{
		$pdf = $this->createPdf();

		$this->generateTicketContainer($pdf, $internalTicket, "Journey coupon", false, true);
		$pdf->Ln(27);
		$this->generateTicketContainer($pdf, $internalTicket, "Customer receipt", true, true);
		$pdf->Ln(25);
		$this->generateTicketContainer($pdf, $internalTicket, "Auditor coupon", true, false);
		$pdf->Ln(25);
		$this->generateTicketContainer($pdf, $internalTicket, "Auditor coupon", true, false);

		return $pdf;
	}

	/**
	 * @param PDF $pdf
	 * @param OrderPerson $orderPerson
	 * @param Route $route
	 * @param string $ticketTitle
	 * @param bool $isForOwner
	 */
	private function generateTicketContainer(PDF $pdf, \AppBundle\Entity\InternalTicket $internalTicket, $ticketTitle, $isForOwner = false, $additionalInfo = true)
	{
		$orderPerson = $internalTicket->getOrderPerson();
		$order = $internalTicket->getOrder();
		$route = $internalTicket->getRoute();
		$routeTariff = $internalTicket->getRouteTariff();

		$english = $this->languageService->getEnglish();

		$pdf->SetFontSize(15);
		$pdf->Cell(50, 10, "EUROTOURS s.r.o.", 0, 0, "C");
		$pdf->SetFontSize(7);
		$pdf->Cell(75, 10, "TEL.: 00420 724 132365 ||| EMAIL: info@eurotours.cz", 0, 0, "C");
		$pdf->SetFontSize(9);
		$pdf->Cell(65, 10, $ticketTitle . " " . $order->getIdent(), 0, 1, "C", true);
		$pdf->Cell(40, 4, "Jméno (Name):", 0, 0, "C", true);
		$pdf->Cell(150, 4, $orderPerson->getName(), 0, 1, "L", true);

		if ($isForOwner) {
			$pdf->Cell(40, 8, "Z (From):", 0, 0, "C");
			$pdf->Cell(150, 4, $order->getRouteThere()->getSimpleFromCityName()->getString($english). ", " . $order->getRouteThere()->getFromStationName()->getString($english), 0, 2, "L");
			$pdf->Cell(150, 4, $order->getRouteThere()->getDatetimeDeparture()->format("j. n. Y H:i"), 0, 1, "L");
			$pdf->Cell(40, 8, "Do (To):", 0, 0, "C", true);
			$pdf->Cell(150, 4, $order->getRouteThere()->getSimpleToCityName()->getString($english). ", " . $order->getRouteThere()->getToStationName()->getString($english), 0, 2, "L", true);
			$pdf->Cell(150, 4, $order->getRouteThere()->getDatetimeArrival()->format("j. n. Y H:i"), 0, 1, "L", true);
			$pdf->Cell(40, 4, "Dopravce (Company):", 0, 0, "C");
			$pdf->Cell(150, 4, $order->getRouteThere()->getCarrier()->getName(), 0, 1, "L");

			if ($order->getRouteBack()) {
				$pdf->Cell(40, 8, "Z (From):", 0, 0, "C", true);
				$pdf->Cell(150, 4, $order->getRouteBack()->getSimpleFromCityName()->getString($english). ", " . $order->getRouteBack()->getFromStationName()->getString($english), 0, 2, "L", true);
				$pdf->Cell(150, 4, $order->getRouteBack()->getDatetimeDeparture()->format("j. n. Y H:i"), 0, 1, "L", true);
				$pdf->Cell(40, 8, "Do (To):", 0, 0, "C");
				$pdf->Cell(150, 4, $order->getRouteBack()->getSimpleToCityName()->getString($english). ", " . $order->getRouteBack()->getToStationName()->getString($english), 0, 2, "L");
				$pdf->Cell(150, 4, $order->getRouteBack()->getDatetimeArrival()->format("j. n. Y H:i"), 0, 1, "L");
				$pdf->Cell(40, 4, "Dopravce (Company):", 0, 0, "C", true);
				$pdf->Cell(150, 4, $order->getRouteBack()->getCarrier()->getName(), 0, 1, "L", true);
			}

		} else {
			$fromString = $route->getSimpleFromCityName()->getString($english)
				. ", " . $route->getFromStationName()->getString($english);
			$toString = $route->getSimpleToCityName()->getString($english)
				. ", " . $route->getToStationName()->getString($english);

			$pdf->Cell(40, 8, "Z (From):", 0, 0, "C");
			$pdf->Cell(150, 4, $fromString, 0, 2, "L");
			$pdf->Cell(150, 4, $route->getDatetimeDeparture()->format("j. n. Y H:i"), 0, 1, "L");
			$pdf->Cell(40, 8, "Do (To):", 0, 0, "C", true);
			$pdf->Cell(150, 4, $toString, 0, 2, "L", true);
			$pdf->Cell(150, 4, $route->getDatetimeArrival()->format("j. n. Y H:i"), 0, 1, "L", true);
			$pdf->Cell(40, 4, "Dopravce (Company):", 0, 0, "C");
			$pdf->Cell(150, 4, $route->getCarrier()->getName(), 0, 1, "L");
		}

		$pdf->Cell(40, 4, "Cena (Price):", 0, 0, "C", !$isForOwner);
		$pdf->Cell(150, 4, $this->priceCurrencyExtension->priceCurrency($routeTariff->getPriceCurrency()), 0, 1, "L", !$isForOwner);

		if (!($internalTicket->getSchedule()->isSeatsWithoutNumbers() or $internalTicket->getSchedule()->getLine()->isSeatsWithoutNumbers($internalTicket->getSchedule()->getDirection()))) {
			$pdf->Cell(40, 4, "Sedadlo:", 0, 0, "C", $isForOwner);
			$pdf->Cell(150, 4, $internalTicket->getNumber(), 0, 1, "L", $isForOwner);
		}

		if ($additionalInfo) {
			$pdf->SetFontSize(7);
			$pdf->Cell(190, 8, "Cestující je povinen se dostavit k odbavení 20 minut před odjezdem.", 0, 1, "C");
			$pdf->SetFontSize(9);
			$pdf->Cell(190, 1, "", "T", 1, "C");
		}
	}

	/**
	 * @return PDF
	 */
	private function createPdf()
	{
		$pdf = new PDF();
		$pdf->SetCreator("Eurotours s.r.o.", true);
		$pdf->SetAuthor("Eurotours s.r.o.", true);
		$pdf->SetSubject("Jízdenka", true);
		$pdf->SetTitle("Jízdenka", true);
		$pdf->SetDisplayMode("real", "continuous");
		$pdf->AddFont("DejaVuSans", "", "DejaVuSans.ttf", true);
		$pdf->SetFont("DejaVuSans", "", 9);
		$pdf->SetFillColor(210);
		$pdf->AddPage();

		return $pdf;
	}

	/**
	 * @param SimpleInternalTicket $thereTicket
	 * @param SimpleInternalTicket|null $backTicket
	 * @return PDF
	 */
	public function generateDummyTicket(SimpleInternalTicket $thereTicket, $backTicket)
	{
		$pdf = $this->createPdf();

		$this->generateDummyTicketContainer($pdf, $thereTicket, $backTicket, "First journey coupon");
		if ($backTicket !== null) {
			$this->generateDummyTicketContainer($pdf, $backTicket, $backTicket, "Second journey coupon");
		}
		$this->generateDummyTicketContainer($pdf, $thereTicket, $backTicket, "Customer receipt", true);
		$this->generateDummyTicketContainer($pdf, $thereTicket, $backTicket, "Auditor coupon", true);
		$this->generateDummyTicketContainer($pdf, $thereTicket, $backTicket, "Auditor coupon", true);

		return $pdf;
	}

	/**
	 * @param PDF $pdf
	 * @param SimpleInternalTicket $thereTicket
	 * @param SimpleInternalTicket $backTicket
	 * @param string $ticketTitle
	 * @param bool $isForOwner
	 */
	private function generateDummyTicketContainer(PDF $pdf, SimpleInternalTicket $thereTicket, $backTicket, $ticketTitle, $isForOwner = false)
	{
		$pdf->SetFontSize(15);
		$pdf->Cell(50, 10, "EUROTOURS s.r.o.", 0, 0, "C");
		$pdf->SetFontSize(7);
		$pdf->Cell(75, 10, "TEL.: 00420 724 132365 ||| EMAIL: info@eurotours.cz", 0, 0, "C");
		$pdf->SetFontSize(9);
		$pdf->Cell(65, 10, $ticketTitle . " " . $thereTicket->getIdent(), 0, 1, "C", true);
		$pdf->Cell(40, 4, "Jméno (Name):", 0, 0, "C", true);
		$pdf->Cell(150, 4, $thereTicket->getName(), 0, 1, "L", true);

		$price = $thereTicket->getPrice();
		if ($backTicket !== null) $price += $backTicket->getPrice();

		if ($isForOwner) {
			$pdf->Cell(40, 8, "Z (From):", 0, 0, "C");
			$pdf->Cell(150, 4, $thereTicket->getFrom(), 0, 2, "L");
			$pdf->Cell(150, 4, $thereTicket->getDatetimeDeparture()->format("j. n. Y H:i"), 0, 1, "L");
			$pdf->Cell(40, 8, "Do (To):", 0, 0, "C", true);
			$pdf->Cell(150, 4, $thereTicket->getTo(), 0, 2, "L", true);
			$pdf->Cell(150, 4, $thereTicket->getDatetimeArrival()->format("j. n. Y H:i"), 0, 1, "L", true);
			$pdf->Cell(40, 4, "Dopravce (Company):", 0, 0, "C");
			$pdf->Cell(150, 4, $thereTicket->getCarrier(), 0, 1, "L");

			if ($backTicket !== null) {
				$pdf->Cell(40, 8, "Z (From):", 0, 0, "C", true);
				$pdf->Cell(150, 4, $backTicket->getFrom(), 0, 2, "L", true);
				$pdf->Cell(150, 4, $backTicket->getDatetimeDeparture()->format("j. n. Y H:i"), 0, 1, "L", true);
				$pdf->Cell(40, 8, "Do (To):", 0, 0, "C");
				$pdf->Cell(150, 4, $backTicket->getTo(), 0, 2, "L");
				$pdf->Cell(150, 4, $backTicket->getDatetimeArrival()->format("j. n. Y H:i"), 0, 1, "L");
				$pdf->Cell(40, 4, "Dopravce (Company):", 0, 0, "C", true);
				$pdf->Cell(150, 4, $backTicket->getCarrier(), 0, 1, "L", true);
			}

		} else {
			$pdf->Cell(40, 8, "Z (From):", 0, 0, "C");
			$pdf->Cell(150, 4, $thereTicket->getFrom(), 0, 2, "L");
			$pdf->Cell(150, 4, $thereTicket->getDatetimeDeparture()->format("j. n. Y H:i"), 0, 1, "L");
			$pdf->Cell(40, 8, "Do (To):", 0, 0, "C", true);
			$pdf->Cell(150, 4, $thereTicket->getTo(), 0, 2, "L", true);
			$pdf->Cell(150, 4, $thereTicket->getDatetimeArrival()->format("j. n. Y H:i"), 0, 1, "L", true);
			$pdf->Cell(40, 4, "Dopravce (Company):", 0, 0, "C");
			$pdf->Cell(150, 4, $thereTicket->getCarrier(), 0, 1, "L");
		}

		$pdf->Cell(40, 4, "Cena (Price):", 0, 0, "C", !$isForOwner);
		$pdf->Cell(150, 4, $price . " Kč", 0, 1, "L", !$isForOwner);

		$pdf->SetFontSize(7);
		$pdf->Cell(190, 8, "Cestující je povinen se dostavit k odbavení 20 minut před odjezdem.", 0, 1, "C");
		$pdf->SetFontSize(9);
		$pdf->Cell(190, 1, "", "T", 1, "C");
	}

	/**
	 * @param \AppBundle\Entity\InternalTicket $internalTicket
	 * @deprecated
	 */
	public function generateFile(\AppBundle\Entity\InternalTicket $internalTicket)
	{
		$file = $this->uploadService->createFile("pdf");
		file_put_contents($this->uploadService->getWebDir() . $file, $this->generateTicket($internalTicket)->output());
		$internalTicket->setFile($file);
	}
}
