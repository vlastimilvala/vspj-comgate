<?php

declare(strict_types=1);

namespace Vspj\PlatebniBrana\Comgate\Base;

use Comgate\SDK\Entity\Codes\PaymentStatusCode;
use DateTime;

class ComgatePlatbaStav
{
    public const COMGATE_PLATBA_STAV_ZAPLACENO_ID = PaymentStatusCode::PAID;
    public const COMGATE_PLATBA_STAV_ZAPLACENO_POPIS = 'Platba proběhla úspěšně';
    public const COMGATE_PLATBA_STAV_ZAPLACENO_ZVYRAZNENI = 'success';

    public const COMGATE_PLATBA_STAV_ZRUSENO_ID = PaymentStatusCode::CANCELLED;
    public const COMGATE_PLATBA_STAV_ZRUSENO_POPIS = 'Požadavek byl zrušen';
    public const COMGATE_PLATBA_STAV_ZRUSENO_ZVYRAZNENI = 'danger';

    public const COMGATE_PLATBA_STAV_CEKAJICI_ID = PaymentStatusCode::PENDING;
    public const COMGATE_PLATBA_STAV_CEKAJICI_POPIS = 'Čekáme na dokončení platby';
    public const COMGATE_PLATBA_STAV_CEKAJICI_ZVYRAZNENI = 'info';

    public const COMGATE_PLATBA_STAV_AUTORIZOVANO_ID = PaymentStatusCode::AUTHORIZED;
    public const COMGATE_PLATBA_STAV_AUTORIZOVANO_POPIS = 'Platba byla úspěšně autorizována';
    public const COMGATE_PLATBA_STAV_AUTORIZOVANO_ZVYRAZNENI = 'info';

    private string $transakceId;

    //vala04 - Jedná se o specifický symbol ze strany klienta
    private string $ssKlient;

    //vala04 - Jedná se o variabilní symbol ze strany klienta
    private string $vsKlient;

    //vala04 - Jedná se o variabilní symbol, který generuje platební brána
    private ?string $vsBrana;

    private string $metodaPlatby;

    private string $stav;

    private string $popisStavu;

    private string $zvyrazneniStavu;

    private bool $zaplaceno;

    private bool $zruseno;

    private ?DateTime $datumDokonceniPlatby;

    public function __construct(
        string $transakceId,
        string $referenceId,
        string $name,
        string $stav,
        string $popisStavu,
        string $zvyrazneniStavu,
        string $metodaPlatby,
        ?string $vsBrana = null
    ) {
        $this->transakceId = $transakceId;
        $this->ssKlient = $referenceId;
        $this->vsKlient = $name;
        $this->vsBrana = $vsBrana;
        $this->stav = $stav;
        $this->popisStavu = $popisStavu;
        $this->zvyrazneniStavu = $zvyrazneniStavu;
        $this->metodaPlatby = $metodaPlatby;
        $this->zaplaceno = $stav === PaymentStatusCode::PAID;
        $this->zruseno = $stav === PaymentStatusCode::CANCELLED;
        $this->datumDokonceniPlatby = $this->zaplaceno ? new DateTime() : null;
    }

    public function getTransakceId(): string
    {
        return $this->transakceId;
    }

    public function getSsKlient(): ?string
    {
        return $this->ssKlient;
    }

    public function getVsKlient(): ?string
    {
        return $this->vsKlient;
    }

    public function getVsBrana(): ?string
    {
        return $this->vsBrana;
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

    public function getZvyrazneniStavu()
    {
        return $this->zvyrazneniStavu;
    }

    public function jeZaplaceno(): bool
    {
        return $this->zaplaceno;
    }

    public function jeZruseno(): bool
    {
        return $this->zruseno;
    }

    public function getDatumDokonceniPlatby(): ?DateTime
    {
        return $this->datumDokonceniPlatby;
    }
}
