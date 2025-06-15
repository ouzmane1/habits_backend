<?php

namespace App\Entity;

use App\Repository\DefiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DefiRepository::class)]
class Defi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de début est obligatoire.')]
    private ?\DateTime $date_start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de fin est obligatoire.')]
    #[Assert\GreaterThanOrEqual(
        propertyPath: 'date_start', 
        message: 'La date de fin doit être supérieure ou égale à la date de début.'
    )]
    private ?\DateTime $date_end = null;

    #[ORM\Column(length: 255)]
    private ?string $create_by = null;

    /**
     * @var Collection<int, DefiUsers>
     */
    #[ORM\OneToMany(targetEntity: DefiUsers::class, mappedBy: 'defi_id')]
    private Collection $defiUsers;

    /**
     * @var Collection<int, DefiProgress>
     */
    #[ORM\OneToMany(targetEntity: DefiProgress::class, mappedBy: 'defi')]
    private Collection $defiProgress;

    public function __construct()
    {
        $this->defiUsers = new ArrayCollection();
        $this->defiProgress = new ArrayCollection();
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

    /**
     * @return Collection<int, DefiProgress>
     */
    public function getDefiProgress(): Collection
    {
        return $this->defiProgress;
    }

    public function addDefiProgress(DefiProgress $defiProgress): static
    {
        if (!$this->defiProgress->contains($defiProgress)) {
            $this->defiProgress->add($defiProgress);
            $defiProgress->setDefi($this);
        }

        return $this;
    }

    public function removeDefiProgress(DefiProgress $defiProgress): static
    {
        if ($this->defiProgress->removeElement($defiProgress)) {
            // set the owning side to null (unless already changed)
            if ($defiProgress->getDefi() === $this) {
                $defiProgress->setDefi(null);
            }
        }

        return $this;
    }
}
