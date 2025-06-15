<?php

namespace App\Entity;

use App\Repository\ObjectivesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ObjectivesRepository::class)]
class Objectives
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
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
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'objectives')]
    private ?Users $users_id = null;

    /**
     * @var Collection<int, SuiviObjective>
     */
    #[ORM\OneToMany(targetEntity: SuiviObjective::class, mappedBy: 'objective')]
    private Collection $suiviObjectives;

    public function __construct()
    {
        $this->suiviObjectives = new ArrayCollection();
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
     * @return Collection<int, SuiviObjective>
     */
    public function getSuiviObjectives(): Collection
    {
        return $this->suiviObjectives;
    }

    public function addSuiviObjective(SuiviObjective $suiviObjective): static
    {
        if (!$this->suiviObjectives->contains($suiviObjective)) {
            $this->suiviObjectives->add($suiviObjective);
            $suiviObjective->setObjective($this);
        }

        return $this;
    }

    public function removeSuiviObjective(SuiviObjective $suiviObjective): static
    {
        if ($this->suiviObjectives->removeElement($suiviObjective)) {
            // set the owning side to null (unless already changed)
            if ($suiviObjective->getObjective() === $this) {
                $suiviObjective->setObjective(null);
            }
        }

        return $this;
    }
}
