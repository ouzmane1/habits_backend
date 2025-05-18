<?php

namespace App\Entity;

use App\Repository\DefiUsersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DefiUsersRepository::class)]
class DefiUsers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $point = null;

    #[ORM\Column]
    private ?int $ranking = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_inscription = null;

    #[ORM\ManyToOne(inversedBy: 'defiUsers')]
    private ?Users $users_id = null;

    #[ORM\ManyToOne(inversedBy: 'defiUsers')]
    private ?Defi $defi_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPoint(): ?int
    {
        return $this->point;
    }

    public function setPoint(int $point): static
    {
        $this->point = $point;

        return $this;
    }

    public function getRanking(): ?int
    {
        return $this->ranking;
    }

    public function setRanking(int $ranking): static
    {
        $this->ranking = $ranking;

        return $this;
    }

    public function getDateInscription(): ?\DateTime
    {
        return $this->date_inscription;
    }

    public function setDateInscription(\DateTime $date_inscription): static
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }

    public function getUsersId(): ?Users
    {
        return $this->users_id;
    }

    public function setUsersId(?Users $users_id): static
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getDefiId(): ?Defi
    {
        return $this->defi_id;
    }

    public function setDefiId(?Defi $defi_id): static
    {
        $this->defi_id = $defi_id;

        return $this;
    }
}
