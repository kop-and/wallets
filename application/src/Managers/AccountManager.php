<?php

namespace App\Managers;

use App\Entity\Account;
use App\Entity\User;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;

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
}
