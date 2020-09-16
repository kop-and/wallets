<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionsRepository")
 */
class Transaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Wallet
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet", inversedBy="ransactions")
     * @ORM\JoinColumn(name="wallet_id", referencedColumnName="id", nullable=true)
     */
    private $wallet;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Transaction
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return Wallet
     */
    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    /**
     * @param Wallet $wallet
     * @return Transaction
     */
    public function setWallet(Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }
}
