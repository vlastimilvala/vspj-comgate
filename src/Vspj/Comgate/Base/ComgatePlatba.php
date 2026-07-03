<?php

declare(strict_types=1);

namespace Vspj\PlatebniBrana\Comgate\Base;

class ComgatePlatba
{
    private string $specifickySymbol;

    private string $variabilniSymbol;

    private string $celeJmenoPlatce;

    private string $emailPlatce;

    private string $popisPlatby;

    private string $expiracePlatby;

    private bool $pouzePlatbaKartou;

    private bool $umoznitBeznyBankovniPrevod;

    private float $castkaCzk;

    /**
     * @param string $specifickySymbol SS platby
     * @param string $variabilniSymbol VS platby
     * @param string $celeJmenoPlatce Celé jméno plátce
     * @param string $emailPlatce E-mail plátce
     * @param string $popisPlatby Popis určení platby
     * @param float $castkaCzk Částka transakce - např. 50,25 Kč napsat jako hodnotu 50.25
     * @param string $expiracePlatby Nepovinný parametr pro expiraci platby (povolené hodnoty např. '30m', '1h', '2d' apod.)
     * @param bool $pouzePlatbaKartou Uživateli se při vstupu na platební bránu zobrazí platba kartou jako primární metoda
     * @param bool $umoznitBeznyBankovniPrevod Uživatel bude mít k dispozici volbu 'Ostatní banky' pro běžný převod. Expirace platby musí byt nastavena minimálně na 4d.
     */
    public function __construct(
        string $specifickySymbol,
        string $variabilniSymbol,
        string $celeJmenoPlatce,
        string $emailPlatce,
        string $popisPlatby,
        float $castkaCzk,
        string $expiracePlatby = '',
        bool $pouzePlatbaKartou = false,
        bool $umoznitBeznyBankovniPrevod = false
    ) {
        $this->specifickySymbol = $specifickySymbol;
        $this->variabilniSymbol = $variabilniSymbol;
        $this->celeJmenoPlatce = $celeJmenoPlatce;
        $this->emailPlatce = $emailPlatce;
        $this->popisPlatby = $popisPlatby;
        $this->castkaCzk = $castkaCzk;
        $this->expiracePlatby = $expiracePlatby;
        $this->pouzePlatbaKartou = $pouzePlatbaKartou;
        $this->umoznitBeznyBankovniPrevod = $umoznitBeznyBankovniPrevod;
    }

    public function getSpecifickySymbol(): string
    {
        return $this->specifickySymbol;
    }

    public function getVariabilniSymbol(): string
    {
        return $this->variabilniSymbol;
    }

    public function getCeleJmenoPlatce(): string
    {
        return $this->celeJmenoPlatce;
    }

    public function getEmailPlatce(): string
    {
        return $this->emailPlatce;
    }

    public function getPopisPlatby(): string
    {
        return $this->popisPlatby;
    }

    public function getCastkaCzk(): float
    {
        return $this->castkaCzk;
    }

    public function getExpiracePlatby(): string
    {
        return $this->expiracePlatby;
    }

    public function isPouzePlatbaKartou(): bool
    {
        return $this->pouzePlatbaKartou;
    }

    public function umoznitBeznyBankovniPrevod(): bool
    {
        return $this->umoznitBeznyBankovniPrevod;
    }
}
