<?php
declare(strict_types=1);

namespace App\Managers;

use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Entity\Commission;
use App\Entity\User;
use App\Repository\CommissionRepository;
use App\Repository\WalletRepository;
use App\Services\CalculationTransfer;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
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
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * WalletManager constructor.
     * @param EntityManagerInterface $em
     * @param CommissionRepository $commissionRepository
     * @param CalculationTransfer $calculationTransfer
     * @param WalletRepository $walletRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        CommissionRepository $commissionRepository,
        CalculationTransfer $calculationTransfer,
        WalletRepository $walletRepository
    )
    {
        $this->em = $em;
        $this->commissionRepository = $commissionRepository;
        $this->calculationTransfer = $calculationTransfer;
        $this->walletRepository = $walletRepository;
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
     * @param int $fromWalletId
     * @param int $toWalletId
     * @param int $amountTransfer
     * @return array
     */
    public function transferAmount(int $fromWalletId, int $toWalletId, int $amountTransfer): array
    {
        /** @var Commission $commission */
        $commission = $this->commissionRepository->findOneBy(['type' => Commission::TYPE_TRANSACTION_USER]);

        $amount = $this->calculationTransfer->calculation(
            $amountTransfer,
            $commission->getValue()
        );

        return $this->transfer($fromWalletId, $toWalletId, $amountTransfer, $amount);
    }

    /**
     * @param int $fromWalletId
     * @param int $toWalletId
     * @param int $amountTransfer
     * @param int $amount
     * @return array
     */
    private function transfer(int $fromWalletId, int $toWalletId, int $amountTransfer, int $amount): array
    {
        $this->em->beginTransaction();
        
        /** @var Wallet $fromWallet */
        $fromWallet = $this->walletRepository->find($fromWalletId, LockMode::PESSIMISTIC_WRITE);
        /** @var Wallet $toWallet */
        $toWallet = $this->walletRepository->find($toWalletId, LockMode::PESSIMISTIC_WRITE);
        
        if (!$fromWallet) {
            return [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'From Wallet was not found'
            ];
        }
        
        if (!$toWallet) {
            return [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'To Wallet was not found'
            ];
        }
        
        if ($fromWallet->getAmount() < $amount) {
            return [
                'code' => Response::HTTP_METHOD_NOT_ALLOWED,
                'message' => 'The transfer amount is too large, there is not enough amount on the wallet'
            ];
        }
        
        if ($fromWallet->getId() === $toWallet->getId()) {
            return [
                'code' => Response::HTTP_METHOD_NOT_ALLOWED,
                'message' => 'An attempt to transfer to your own wallet'
            ];
        }
        
        try {
            $fromWallet->setAmount($fromWallet->getAmount() - $amount);
            $toWallet->setAmount($toWallet->getAmount() + $amountTransfer);

            $transactionFrom =
                (new Transaction())
                    ->setWallet($fromWallet)
                    ->setAmount(-$amount)
            ;
            $transactionTo =
                (new Transaction())
                    ->setWallet($toWallet)
                    ->setAmount($amountTransfer)
            ;
            $this->em->persist($transactionFrom);
            $this->em->persist($transactionTo);

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $exception) {
            $this->em->rollBack();

            return [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $exception->getMessage()
            ];
        }

        return [
            'code' => Response::HTTP_OK,
            'message' => 'success'
        ];
    }
}
