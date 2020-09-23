<?php
declare(strict_types=1);

namespace App\Services;

class ConvertAmount
{
    public const COEFFICIENT = 100000000;

    /**
     * @param int $satoshi
     * @return float|int
     */
    public function convertFromSatoshiToBitcoin(int $satoshi)
    {
        return $satoshi / self::COEFFICIENT;
    }
}
