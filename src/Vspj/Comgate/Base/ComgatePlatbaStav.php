<?php

declare(strict_types=1);

namespace Vspj\PlatebniBrana\Comgate\Base;

use Comgate\SDK\Entity\Codes\PaymentStatusCode;
use DateTime;

class ComgatePlatbaStav
{
    public const COMGATE_PLATBA_STAV_ZAPLACENO = 'Platba proběhla úspěšně';

    public const COMGATE_PLATBA_STAV_ZRUSENO = 'Požadavek byl zrušen';

    public const COMGATE_PLATBA_STAV_CEKAJICI = 'Čekáme na dokončení platby';

    public const COMGATE_PLATBA_STAV_AUTORIZOVANO = 'Platba byla úspěšně autorizována';

    private string $transakceId;

    //vala04 - Většinou to bude specifický symbol
    private string $referenceId;

	//vala04 - Jedná se o variabilní symbol, který generuje platební brána. Nejedná se tedy o VS ze strany klienta
    private ?string $vs;

    private string $metodaPlatby;

    private string $stav;

    private string $popisStavu;

    private bool $zaplaceno;

    private ?DateTime $datumDokonceniPlatby;

    public function __construct(
        string $transakceId,
        string $referenceId,
        string $stav,
        string $popisStavu,
        string $metodaPlatby,
        ?string $vs = null
    ) {
        $this->transakceId = $transakceId;
        $this->referenceId = $referenceId;
        $this->stav = $stav;
        $this->popisStavu = $popisStavu;
        $this->metodaPlatby = $metodaPlatby;
        $this->vs = $vs;
        $this->zaplaceno = $stav === PaymentStatusCode::PAID;
        $this->datumDokonceniPlatby = $this->zaplaceno ? new DateTime() : null;
    }

    public function getTransakceId(): string
    {
        return $this->transakceId;
    }

    public function getReferenceId(): string
    {
        return $this->referenceId;
    }

    public function getVs(): ?string
    {
        return $this->vs;
    }

    public function getMetodaPlatby(): string
    {
        return $this->metodaPlatby;
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

    public function getDatumDokonceniPlatby(): ?DateTime
    {
        return $this->datumDokonceniPlatby;
    }
}
