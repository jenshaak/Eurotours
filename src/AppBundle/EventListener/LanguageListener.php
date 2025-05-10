<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 20.01.17
 * Time: 20:28
 */

namespace AppBundle\EventListener;


use AppBundle\Service\LanguageService;
use AppBundle\VO\LanguageBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LanguageListener
{
	public function onKernelRequest(GetResponseEvent $event)
	{
		/** @var LanguageBag $languageBag */
		$languageBag = $event->getRequest()->getSession()->getBag(LanguageBag::class);
		if ($languageBag->getLanguage() === null) {
			$languageBag->setLanguageId("cs");
		}

		$event->getRequest()->setLocale($languageBag->getLanguage());
	}
}
