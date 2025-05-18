<?php

namespace App\Entity;

use App\Repository\ObjectivesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ObjectivesRepository::class)]
class Objectives
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_end = null;

    #[ORM\Column]
    private ?float $progres = null;

    #[ORM\Column]
    private ?float $target_value = null;

    #[ORM\Column]
    private ?float $current_value = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'objectives')]
    private ?Users $users_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateStart(): ?\DateTime
    {
        return $this->date_start;
    }

    public function setDateStart(\DateTime $date_start): static
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?\DateTime
    {
        return $this->date_end;
    }

    public function setDateEnd(\DateTime $date_end): static
    {
        $this->date_end = $date_end;

        return $this;
    }

    public function getProgres(): ?float
    {
        return $this->progres;
    }

    public function setProgres(float $progres): static
    {
        $this->progres = $progres;

        return $this;
    }

    public function getTargetValue(): ?float
    {
        return $this->target_value;
    }

    public function setTargetValue(float $target_value): static
    {
        $this->target_value = $target_value;

        return $this;
    }

    public function getCurrentValue(): ?float
    {
        return $this->current_value;
    }

    public function setCurrentValue(float $current_value): static
    {
        $this->current_value = $current_value;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

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
}
