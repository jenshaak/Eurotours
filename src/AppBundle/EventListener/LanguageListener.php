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
use Symfony\Component\Translation\TranslatorInterface;

class LanguageListener
{
	/**
	 * @var TranslatorInterface
	 */
	private $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	public function onKernelRequest(GetResponseEvent $event)
	{
		// Only process master requests
		if (!$event->isMasterRequest()) {
			return;
		}

		$request = $event->getRequest();
		
		// Check if session is available and started
		if (!$request->hasSession()) {
			return;
		}

		$session = $request->getSession();
		
		// Make sure session is started before accessing bags
		if (!$session->isStarted()) {
			try {
				$session->start();
			} catch (\Exception $e) {
				// If session can't be started, just return without setting language
				return;
			}
		}

		try {
			/** @var LanguageBag $languageBag */
			$languageBag = $session->getBag(LanguageBag::class);
			if ($languageBag->getLanguage() === null) {
				$languageBag->setLanguageId("cs");
			}

			$locale = $languageBag->getLanguage();
			
			// Set both request locale and translator locale
			$request->setLocale($locale);
			$this->translator->setLocale($locale);
			
		} catch (\Exception $e) {
			// If there's any issue with the language bag, set a default locale
			$request->setLocale('cs');
			$this->translator->setLocale('cs');
		}
	}
}
