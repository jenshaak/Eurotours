<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.12.17
 * Time: 12:02
 */

namespace AppBundle\Command;


use AppBundle\Entity\Tariff;
use AppBundle\VO\Days;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FillDoctrineObjectTypeCommand extends Command
{
	/**
	 * @var Container
	 */
	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;

		parent::__construct();
	}

	protected function configure()
	{
		$this->setName("fill:doctrine:object-type");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$table = "tariffs";
		$column = "exclude_days";
		$object = new Days;

		/** @var QuestionHelper $helper */
		$helper = $this->getHelper("question");
		$confirm = new ConfirmationQuestion("Table '" . $table . "' and column '" . $column . "' fill by '" . get_class($object) . "' ?\n", false);
		if (!$helper->ask($input, $output, $confirm)) {
			return;
		}

		# process code
		$em = $this->container->get("doctrine.orm.default_entity_manager");
		$query = $em->createNativeQuery("UPDATE " . $table . " SET " . $column . " = ?", new ResultSetMapping());
		$query->execute([ 1 => serialize($object) ]);
	}
}
