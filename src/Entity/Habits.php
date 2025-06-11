<?php

namespace App\Entity;

use App\Enum\FrequenceType;
use App\Repository\HabitsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HabitsRepository::class)]
class Habits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La fréquence est obligatoire.')]
    private ?FrequenceType $frequence = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'habits')]
    private ?Users $users_id = null;

    /**
     * @var Collection<int, Suivihabits>
     */
    #[ORM\OneToMany(targetEntity: Suivihabits::class, mappedBy: 'habits_id')]
    private Collection $suivihabits;

    public function __construct()
    {
        $this->suivihabits = new ArrayCollection();
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

    public function getFrequence(): ?FrequenceType
    {
        return $this->frequence;
    }

    public function setFrequence(?FrequenceType $frequence): static
    {
        $this->frequence = $frequence;

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

    /**
     * @return Collection<int, Suivihabits>
     */
    public function getSuivihabits(): Collection
    {
        return $this->suivihabits;
    }

    public function addSuivihabit(Suivihabits $suivihabit): static
    {
        if (!$this->suivihabits->contains($suivihabit)) {
            $this->suivihabits->add($suivihabit);
            $suivihabit->setHabitsId($this);
        }

        return $this;
    }

    public function removeSuivihabit(Suivihabits $suivihabit): static
    {
        if ($this->suivihabits->removeElement($suivihabit)) {
            // set the owning side to null (unless already changed)
            if ($suivihabit->getHabitsId() === $this) {
                $suivihabit->setHabitsId(null);
            }
        }

        return $this;
    }
}
