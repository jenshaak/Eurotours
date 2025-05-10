<?php

require __DIR__.'/../vendor/autoload.php';

$connector = new \AppBundle\Connectors\StudentAgencyConnector;
var_dump($connector->findSingleRoutes("cs", 10202003, 10202030, new \DateTime));