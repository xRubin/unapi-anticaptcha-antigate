<?php

namespace unapi\anticaptcha\antigate\dto;

use unapi\interfaces\DtoInterface;

interface AntigateBalanceInterface extends DtoInterface
{
    /**
     * @return float
     */
    public function getAmount(): float;
}