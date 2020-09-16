<?php
declare(strict_types=1);

namespace App\Services;


class CalculationTransfer
{
    /**
     * @param int $walletAmount
     * @param int $amountTransfer
     * @param int $commission
     * @return int
     */
    public function calculation(int $walletAmount, int $amountTransfer, int $commission): int
    {
        $newAmount = (int)($walletAmount - ($amountTransfer + $amountTransfer * ($commission / 100)));

        return $newAmount;
    }
}
