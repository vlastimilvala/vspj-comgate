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

    private float $castkaCzk;

    /**
     * @param string $specifickySymbol SS platby
     * @param string $variabilniSymbol VS platby
     * @param string $celeJmenoPlatce Celé jméno plátce
     * @param string $emailPlatce E-mail plátce
     * @param string $popisPlatby Popis určení platby
     * @param float $castkaCzk Částka transakce - např. 50,25 Kč napsat jako hodnotu 50.25
     * @param string $expiracePlatby Nepovinný parametr pro expiraci platby (povolené hodnoty např. '30m', '1h', '2d' apod.)
     */
    public function __construct(
        string $specifickySymbol,
        string $variabilniSymbol,
        string $celeJmenoPlatce,
        string $emailPlatce,
        string $popisPlatby,
        float $castkaCzk,
        string $expiracePlatby = ''
    ) {
        $this->specifickySymbol = $specifickySymbol;
        $this->variabilniSymbol = $variabilniSymbol;
        $this->celeJmenoPlatce = $celeJmenoPlatce;
        $this->emailPlatce = $emailPlatce;
        $this->popisPlatby = $popisPlatby;
        $this->castkaCzk = $castkaCzk;
        $this->expiracePlatby = $expiracePlatby;
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
}
