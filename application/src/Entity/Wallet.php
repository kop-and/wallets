<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WalletRepository")
 * @UniqueEntity("number")
 */
class Wallet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=10, max=30)
     */
    private $number;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="wallets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var ArrayCollection|Transaction[]
     *
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="wallets",
     *     cascade={"all"})
     */
    private $transactions;

    public function __construct()
    {
        $this->amount = 0;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber(string $number): void
    {
        $this->number = $number;
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
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return array|Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions->toArray();
    }

    /**
     * @param array|Wallet[] $transactions
     *
     * @return Wallet
     */
    public function setTransactions(array $transactions): self
    {
        $this->transactions->clear();

        foreach ($transactions as $transaction) {
            $this->addTransaction($transaction);
        }

        return $this;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Wallet
     */
    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $transaction->setWallet($this);
            $this->transactions->add($transaction);
        }

        return $this;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Wallet
     */
    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
        }

        return $this;
    }
}
