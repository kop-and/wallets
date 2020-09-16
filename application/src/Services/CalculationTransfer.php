<?php
declare(strict_types=1);

namespace App\Services;

class CalculationTransfer
{
    /**
     * @param int $amountTransfer
     * @param int $commission
     * @return int
     */
    public function calculation(int $amountTransfer, int $commission): int
    {
        return (int)($amountTransfer + $amountTransfer * ($commission / 100));
    }
}
