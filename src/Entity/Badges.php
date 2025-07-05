<?php

namespace App\Entity;

use App\Repository\BadgesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BadgesRepository::class)]
class Badges
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    private ?string $description = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le code est obligatoire.')]
    private string $code;

    #[ORM\Column(length: 255)]
    private ?string $icon = null;

    /**
     * @var Collection<int, Users>
     */
    #[ORM\ManyToMany(targetEntity: Users::class, inversedBy: 'badges')]
    private Collection $users_id;

    public function __construct()
    {
        $this->users_id = new ArrayCollection();
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

    public function getCode(): string
    {
        return $this->code;
    }
    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, Users>
     */
    public function getUsersId(): Collection
    {
        return $this->users_id;
    }

    public function addUsersId(Users $usersId): static
    {
        if (!$this->users_id->contains($usersId)) {
            $this->users_id->add($usersId);
        }

        return $this;
    }

    public function removeUsersId(Users $usersId): static
    {
        $this->users_id->removeElement($usersId);

        return $this;
    }
}
