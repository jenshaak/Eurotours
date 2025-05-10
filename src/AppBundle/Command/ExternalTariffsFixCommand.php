<?php

namespace AppBundle\Command;

use AppBundle\Entity\ExternalTariff;
use AppBundle\Entity\ExternalTariffBlabla;
use AppBundle\Entity\ExternalTariffEastExpress;
use AppBundle\Entity\ExternalTariffEcolines;
use AppBundle\Entity\ExternalTariffEurolines;
use AppBundle\Entity\ExternalTariffFlixbus;
use AppBundle\Entity\ExternalTariffInfobus;
use AppBundle\Entity\ExternalTariffNikolo;
use AppBundle\Entity\ExternalTariffRegabus;
use AppBundle\Entity\ExternalTariffStudentAgency;
use AppBundle\Entity\ExternalTariffTransTempo;
use AppBundle\Entity\RouteTariff;
use AppBundle\VO\ExternalRouter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExternalTariffsFixCommand extends Command
{
	const EXTERNAL_CONNECTIONS_TARIFF_MAP = [
		ExternalRouter::STUDENT_AGENCY => ExternalTariffStudentAgency::class,
		ExternalRouter::ECOLINES => ExternalTariffEcolines::class,
		ExternalRouter::INFOBUS => ExternalTariffInfobus::class,
		ExternalRouter::EAST_EXPRESS => ExternalTariffEastExpress::class,
		ExternalRouter::FLIXBUS => ExternalTariffFlixbus::class,
		ExternalRouter::EUROLINES => ExternalTariffEurolines::class,
		ExternalRouter::NIKOLO => ExternalTariffNikolo::class,
		ExternalRouter::REGABUS => ExternalTariffRegabus::class,
		ExternalRouter::BLABLA => ExternalTariffBlabla::class,
		ExternalRouter::TRANS_TEMPO => ExternalTariffTransTempo::class,
	];

	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;

		parent::__construct();
	}

	protected function configure()
	{
		$this->setName('eurotours:externalTariffs:fix');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$expr = new Expr();

		foreach (self::EXTERNAL_CONNECTIONS_TARIFF_MAP as $externalConnection => $className) {
			$duplicateExternalTariffIdents = $this->entityManager->getRepository(ExternalTariff::class)
				->createQueryBuilder('et')
				->select('et.ident')
				->andWhere($expr->isInstanceOf('et', $className))
				->groupBy('et.ident')
				->andHaving('COUNT(et) > 1')
				->getQuery()
				->getResult();

			if (empty($duplicateExternalTariffIdents)) continue;

			$externalTariffs = $this->entityManager->getRepository(ExternalTariff::class)
				->createQueryBuilder('et')
				->andWhere(
					$expr->andX(
						$expr->in('et.ident', ':idents'),
						$expr->isInstanceOf('et', $className),
					)
				)
				->addOrderBy('et.id', 'DESC')
				->setParameter('idents', array_map(fn(array $row) => $row['ident'], $duplicateExternalTariffIdents))
				->getQuery()
				->getResult();

			$groupedExternalTariffs = array_reduce(
				$externalTariffs,
				function (array $accumulator, ExternalTariff $externalTariff) {
					if (!array_key_exists($externalTariff->getIdent(), $accumulator)) {
						$accumulator[$externalTariff->getIdent()] = [];
					}

					$accumulator[$externalTariff->getIdent()][] = $externalTariff;

					return $accumulator;
				}, [],
			);

			foreach ($groupedExternalTariffs as $ident => $externalTariffsArr) {
				$keptTariff = $externalTariffsArr[0] ?? null;

				if (!$keptTariff) continue;

				$tariffs = $this->entityManager->getRepository(RouteTariff::class)
					->createQueryBuilder('rt')
					->andWhere(
						$expr->in('rt.externalTariff', ':externalTariffsIds')
					)
					->setParameter(
						'externalTariffsIds', array_map(fn(ExternalTariff $et) => $et->getId(), $externalTariffsArr)
					)
					->getQuery()
					->getResult();

				/** @var RouteTariff $tariff */
				foreach ($tariffs as $tariff) {
					$tariff->setExternalTariff($keptTariff);
				}

				/** @var ExternalTariff $externalTariffItem */
				foreach ($externalTariffsArr as $externalTariffItem) {
					if ($externalTariffItem->getId() === $keptTariff->getId()) continue;

					$this->entityManager->remove($externalTariffItem);
				}
			}

			$this->entityManager->flush();
		}
	}
}
