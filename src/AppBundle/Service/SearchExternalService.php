<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 16:49
 */

namespace AppBundle\Service;


use AppBundle\Entity\Search;
use AppBundle\Entity\SearchExternal;
use AppBundle\Repository\SearchExternalRepository;

class SearchExternalService
{
	/**
	 * @var SearchExternalRepository
	 */
	private $searchExternalRepository;

	public function __construct(SearchExternalRepository $searchExternalRepository)
	{
		$this->searchExternalRepository = $searchExternalRepository;
	}

	/**
	 * @param SearchExternal $searchExternal
	 */
	public function saveSearchExternal(SearchExternal $searchExternal)
	{
		$this->searchExternalRepository->save($searchExternal);
	}

	/**
	 * @param SearchExternal $searchExternal
	 */
	public function softDeleteSearchExternal(SearchExternal $searchExternal)
	{
		$searchExternal->setDeleted(true);
		$this->searchExternalRepository->save($searchExternal);
	}

	/**
	 * @param int $id
	 * @return null|SearchExternal
	 */
	public function getSearchExternal($id)
	{
		return $this->searchExternalRepository->find($id);
	}

	/**
	 * @param Search $search
	 * @return SearchExternal[]
	 */
	public function findSearchExternals(Search $search)
	{
		return $this->searchExternalRepository->findBy([
			"search" => $search,
			"deleted" => false
		]);
	}
}
