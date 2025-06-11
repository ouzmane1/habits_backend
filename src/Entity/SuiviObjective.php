<?php

namespace App\Entity;

use App\Repository\SuiviObjectiveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuiviObjectiveRepository::class)]
class SuiviObjective
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'suiviObjectives')]
    private ?Objectives $objective = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getObjective(): ?Objectives
    {
        return $this->objective;
    }

    public function setObjective(?Objectives $objective): static
    {
        $this->objective = $objective;

        return $this;
    }
}
