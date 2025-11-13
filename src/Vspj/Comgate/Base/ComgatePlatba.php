<?php

declare(strict_types=1);

namespace Vspj\PlatebniBrana\Comgate\Base;

class ComgatePlatba
{
    private string $specifickySymbol;

    private string $celeJmenoPlatce;

    private string $emailPlatce;

    private string $popisPlatby;

    private float $castkaCzk;

    /**
     * @param string $specifickySymbol SS platby
     * @param string $celeJmenoPlatce Celé jméno plátce
     * @param string $emailPlatce E-mail plátce
     * @param string $popisPlatby Popis určení platby
     * @param float $castkaCzk Částka transakce - např. 50,25 Kč napsat jako hodnotu 50.25
     */
    public function __construct(
        string $specifickySymbol,
        string $celeJmenoPlatce,
        string $emailPlatce,
        string $popisPlatby,
        float $castkaCzk
    ) {
        $this->specifickySymbol = $specifickySymbol;
        $this->celeJmenoPlatce = $celeJmenoPlatce;
        $this->emailPlatce = $emailPlatce;
        $this->popisPlatby = $popisPlatby;
        $this->castkaCzk = $castkaCzk;
    }

    public function getSpecifickySymbol(): string
    {
        return $this->specifickySymbol;
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
}
