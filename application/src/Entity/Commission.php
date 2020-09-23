<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommissionRepository")
 */
class Commission
{
    public const TYPE_TRANSACTION_USER = 1;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"walletDetails"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     * @Groups({"walletDetails"})
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer")
     * @Groups({"walletDetails"})
     */
    private $value;

    public function __construct()
    {
        $this->type = self::TYPE_TRANSACTION_USER;
    }

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
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }
}
