<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 16:18
 */

namespace AppBundle\Service;


use AppBundle\Entity\Search;
use AppBundle\Entity\SearchExternal;
use AppBundle\VO\ExternalRouter;
use Predis\Client;

class ExternalRouteService
{
	/**
	 * @var Client
	 */
	private $redis;
	/**
	 * @var SearchExternalService
	 */
	private $searchExternalService;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;

	public function __construct(Client $redis,
	                            SearchExternalService $searchExternalService,
	                            CurrencyService $currencyService)
	{
		$this->redis = $redis;
		$this->searchExternalService = $searchExternalService;
		$this->currencyService = $currencyService;
	}

	/**
	 * @param Search $search
	 */
	public function callExternalRoutes(Search $search)
	{
		$currency = $this->currencyService->getCurrentCurrency();

		$search->getSearchExternals()->map(
			fn (SearchExternal $i) => $this->searchExternalService->softDeleteSearchExternal($i)
		);

		/** @var SearchExternal[] $externals */
		$externals = [
			SearchExternal::create($search, ExternalRouter::INFOBUS, $currency),
			SearchExternal::create($search, ExternalRouter::EAST_EXPRESS, $currency),
			SearchExternal::create($search, ExternalRouter::FLIXBUS, $currency),
			SearchExternal::create($search, ExternalRouter::BLABLA, $currency),
			SearchExternal::create($search, ExternalRouter::TRANS_TEMPO, $currency),
			SearchExternal::create($search, ExternalRouter::NIKOLO, $currency),
			SearchExternal::create($search, ExternalRouter::LIKEBUS, $currency)
		];

		foreach ($externals as $external) {
			$this->searchExternalService->saveSearchExternal($external);
			$this->redis->rpush($external->getType(), [ $external->getId() ]);
		}
	}
}
