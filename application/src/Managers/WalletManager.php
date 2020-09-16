<?php
declare(strict_types=1);

namespace App\Managers;

use App\Entity\Wallet;
use App\Entity\Commission;
use App\Entity\User;
use App\Repository\CommissionRepository;
use App\Services\CalculationTransfer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class WalletManager
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var CommissionRepository
     */
    private $commissionRepository;

    /**
     * @var CalculationTransfer
     */
    private $calculationTransfer;

    /**
     * WalletManager constructor.
     * @param EntityManagerInterface $em
     * @param CommissionRepository $commissionRepository
     * @param CalculationTransfer $calculationTransfer
     */
    public function __construct(
        EntityManagerInterface $em,
        CommissionRepository $commissionRepository,
        CalculationTransfer $calculationTransfer
    )
    {
        $this->em = $em;
        $this->commissionRepository = $commissionRepository;
        $this->calculationTransfer = $calculationTransfer;
    }

    /**
     * @param Wallet $wallet
     * @param User $user
     * @return Wallet
     */
    public function createWallet(Wallet $wallet, User $user): Wallet
    {
        if (count($user->getwallets()) > User::MAX_WALLETS) {
            throw new MethodNotAllowedHttpException(
                [],
                "Can't create more than " . User::MAX_WALLETS . " wallets"
            );
        }
        $wallet->setUser($user);
        $this->em->persist($wallet);
        $this->em->flush();

        return $wallet;
    }

    /**
     * @param Wallet $fromWallet
     * @param Wallet $toWallet
     * @param int $amountTransfer
     * @return bool
     */
    public function transferAmount(Wallet $fromWallet, Wallet $toWallet, int $amountTransfer): bool
    {
        /** @var Commission $commission */
        $commission = $this->commissionRepository->findOneBy(['type' => Commission::TYPE_TRANSACTION_USER]);

        if ($fromWallet->getAmount() < ($amountTransfer + $amountTransfer * ($commission->getValue() / 100))) {
            throw new MethodNotAllowedHttpException(
                [],
                'The transfer amount is too large, there is not enough amount on the wallet'
            );
        }

        if ($fromWallet->getId() === $toWallet->getId()) {
            throw new MethodNotAllowedHttpException([], 'An attempt to transfer to your own wallet');
        }

        $newAmount = $this->calculationTransfer->calculation($fromWallet->getAmount(), $amountTransfer, $commission->getValue());

        $fromWallet->setAmount($newAmount);
        $toWallet->setAmount($toWallet->getAmount() + $amountTransfer);

        $this->em->flush();

        return true;
    }
}
