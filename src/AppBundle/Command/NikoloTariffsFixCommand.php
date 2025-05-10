<?php


namespace AppBundle\Command;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NikoloTariffsFixCommand extends Command
{
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct();
		$this->entityManager = $entityManager;
	}

	protected function configure()
	{
		$this->setName("eurotours:tmp:fix:nikolo");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$query = $this->entityManager->getConnection()->prepare("SELECT ident, COUNT(external_tariff_id) FROM external_tariffs WHERE type = 'nikolo' GROUP BY ident HAVING COUNT(external_tariff_id) > 1 ");
		$query->execute();

		foreach ($query->fetchFirstColumn() as $ident) {
			$query = $this->entityManager->getConnection()->prepare("SELECT external_tariff_id FROM external_tariffs WHERE type = 'nikolo' AND ident = '${ident}'");
			$query->execute();
			$ids = array_map(function ($i) { return (int) $i; }, $query->fetchFirstColumn());
			$minId = min($ids);

			array_map(function ($id) use ($minId) {
				$this->entityManager->getConnection()->executeQuery(
					"UPDATE routes_tariffs SET external_tariff_id = '$minId' WHERE external_tariff_id = '$id'"
				);
			}, $ids);

			array_map(function ($id) {
				try {
					$this->entityManager->getConnection()->executeQuery(
						"DELETE FROM external_tariffs WHERE external_tariff_id = '$id'"
					);
				} catch (\Exception $e) {}
			}, $ids);
		}

		$query = $this->entityManager->getConnection()->prepare("SELECT external_tariff_id FROM external_tariffs WHERE external_tariff_id NOT IN (SELECT external_tariff_id FROM routes_tariffs WHERE external_tariff_id IS NOT NULL)");
		$query->execute();
		foreach ($query->fetchFirstColumn() as $id) {
			$this->entityManager->getConnection()->executeQuery(
				"DELETE FROM external_tariffs WHERE external_tariff_id = '$id'"
			);
		}
	}
}
