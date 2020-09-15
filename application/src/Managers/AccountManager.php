<?php

namespace App\Managers;

use App\Entity\Account;
use App\Entity\Commission;
use App\Entity\User;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class AccountManager
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var AccountRepository $repository
     */
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository(Account::class);
    }

    /**
     * @param Account $account
     * @param User $user
     * @return Account
     */
    public function createAccount(Account $account, User $user): Account
    {
        $account->setUser($user);
        $this->em->persist($account);
        $this->em->flush();

        return $account;
    }

    /**
     * @param Account $fromAccount
     * @param Account $toAccount
     * @param int $amountTransfer
     * @return bool
     */
    public function transferAmount(Account $fromAccount, Account $toAccount, int $amountTransfer)
    {
        /**
         * @var Commission $commission
         */
        $commission = $this->em->getRepository(Commission::class)->findOneBy(['type' => Commission::TYPE_TRANSACTION_USER]);

        if ($fromAccount->getAmount() < ($amountTransfer + $amountTransfer * $commission->getValue())) {
            throw new MethodNotAllowedHttpException([], 'The transfer amount is too large, there is not enough amount on the wallet');
        }

        if ($fromAccount->getId() === $toAccount->getId()) {
            throw new MethodNotAllowedHttpException([], 'An attempt to transfer to your own account');
        }

        $fromAccount->setAmount($fromAccount->getAmount() - ($amountTransfer + $amountTransfer * $commission->getValue()));
        $toAccount->setAmount($toAccount->getAmount() + $amountTransfer);

        $this->em->persist($fromAccount);
        $this->em->persist($toAccount);
        $this->em->flush();

        return true;
    }
}
