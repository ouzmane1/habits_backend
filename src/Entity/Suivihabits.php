<?php

namespace App\Entity;

use App\Repository\SuivihabitsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuivihabitsRepository::class)]
class Suivihabits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?bool $finish = null;

    #[ORM\ManyToOne(inversedBy: 'suivihabits')]
    private ?Habits $habits_id = null;

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

    public function isFinish(): ?bool
    {
        return $this->finish;
    }

    public function setFinish(bool $finish): static
    {
        $this->finish = $finish;

        return $this;
    }

    public function getHabitsId(): ?Habits
    {
        return $this->habits_id;
    }

    public function setHabitsId(?Habits $habits_id): static
    {
        $this->habits_id = $habits_id;

        return $this;
    }
}
