<?php

namespace AppBundle\VO;

class ExternalRouteLikeBus
{
	private array $priceList;

	private ?string $changedToCurrency = null;

	public function getPriceList(): array
	{
		return $this->priceList;
	}

	public function setPriceList(array $priceList): void
	{
		$this->priceList = $priceList;
	}

	public function getChangedToCurrency(): ?string
	{
		return $this->changedToCurrency;
	}

	public function setChangedToCurrency(?string $changedToCurrency): void
	{
		$this->changedToCurrency= $changedToCurrency;
	}
}
