<?php declare(strict_types = 1);

namespace Vspj\Comgate\Base;

use Comgate\SDK\Entity\Codes\PaymentStatusCode;

class ComgatePlatbaStav
{

	public const COMGATE_PLATBA_STAV_ZAPLACENO = 'Platba proběhla úspěšně';

	public const COMGATE_PLATBA_STAV_ZRUSENO = 'Požadavek byl zrušen';

	public const COMGATE_PLATBA_STAV_CEKAJICI = 'Čekáme na dokončení platby';

	public const COMGATE_PLATBA_STAV_AUTORIZOVANO = 'Platba byla úspěšně autorizována';

	private string $platbaId;

	//vala04 - většinou to bude specifický symbol
	private string $referenceId;

	private string $stav;

	private string $popisStavu;

	private bool $zaplaceno;

	public function __construct(string $platbaId, string $referenceId, string $stav, string $popisStavu)
	{
		$this->platbaId = $platbaId;
		$this->referenceId = $referenceId;
		$this->stav = $stav;
		$this->popisStavu = $popisStavu;
		$this->zaplaceno = $stav === PaymentStatusCode::PAID;
	}

	public function getPlatbaId(): string
	{
		return $this->platbaId;
	}

	public function getReferenceId(): string
	{
		return $this->referenceId;
	}

	public function getStav(): string
	{
		return $this->stav;
	}

	public function getPopisStavu(): string
	{
		return $this->popisStavu;
	}

	public function jeZaplaceno(): bool
	{
		return $this->zaplaceno;
	}

}
