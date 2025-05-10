<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 11:38
 */

namespace AppBundle\Service;


use AppBundle\Entity\Search;
use AppBundle\Repository\SearchRepository;

class SearchService
{
	/**
	 * @var SearchRepository
	 */
	private $searchRepository;

	public function __construct(SearchRepository $searchRepository)
	{
		$this->searchRepository = $searchRepository;
	}

	/**
	 * @param Search $search
	 */
	public function saveSearch(Search $search)
	{
		$this->searchRepository->save($search);
	}

	/**
	 * @param int $id
	 * @return null|Search
	 */
	public function getSearch($id)
	{
		return $this->searchRepository->find($id);
	}
}