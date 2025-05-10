<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SendTestEmailCommand extends Command
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TwigEngine
     */
    private $twigEngine;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(\Swift_Mailer $mailer, TwigEngine $twigEngine, ContainerInterface $container)
    {
        parent::__construct();
        $this->mailer = $mailer;
        $this->twigEngine = $twigEngine;
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setName('app:send-test-email')
            ->setDescription('Odešle testovací e-mail na adam@motvicka.cz')
            ->setHelp('Tento příkaz odešle testovací e-mail s textem "testovací e-mail" a předmětem "testovací subjekt"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Odesílám testovací e-mail...');

        $message = $this->mailer->createMessage()
            ->setSubject('testovací subjekt')
            ->setFrom($this->container->getParameter('email_sender'))
            ->setTo('adam@motvicka.cz')
            ->setBody('testovací e-mail', 'text/html');

        $this->mailer->send($message);

        $output->writeln('E-mail byl úspěšně odeslán!');
    }
}
