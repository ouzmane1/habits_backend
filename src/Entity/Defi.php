<?php

namespace App\Entity;

use App\Repository\DefiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DefiRepository::class)]
class Defi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_end = null;

    #[ORM\Column(length: 255)]
    private ?string $create_by = null;

    /**
     * @var Collection<int, DefiUsers>
     */
    #[ORM\OneToMany(targetEntity: DefiUsers::class, mappedBy: 'defi_id')]
    private Collection $defiUsers;

    public function __construct()
    {
        $this->defiUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getCreateBy(): ?string
    {
        return $this->create_by;
    }

    public function setCreateBy(string $create_by): static
    {
        $this->create_by = $create_by;

        return $this;
    }

    /**
     * @return Collection<int, DefiUsers>
     */
    public function getDefiUsers(): Collection
    {
        return $this->defiUsers;
    }

    public function addDefiUser(DefiUsers $defiUser): static
    {
        if (!$this->defiUsers->contains($defiUser)) {
            $this->defiUsers->add($defiUser);
            $defiUser->setDefiId($this);
        }

        return $this;
    }

    public function removeDefiUser(DefiUsers $defiUser): static
    {
        if ($this->defiUsers->removeElement($defiUser)) {
            // set the owning side to null (unless already changed)
            if ($defiUser->getDefiId() === $this) {
                $defiUser->setDefiId(null);
            }
        }

        return $this;
    }
}
