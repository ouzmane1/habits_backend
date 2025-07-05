<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('email', message: 'Cet email est déjà utilisé.')]
#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'email "{{ value }}" n\'est pas valide.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'email ne doit pas dépasser {{ limit }} caractères.'
    )]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
        max: 255,
        maxMessage: 'Le mot de passe ne doit pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).+$/',
        message: 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.'
    )]
    private ?string $password = null;

    /**
     * @var Collection<int, Objectives>
     */
    #[ORM\OneToMany(targetEntity: Objectives::class, mappedBy: 'users_id')]
    private Collection $objectives;

    /**
     * @var Collection<int, Notifications>
     */
    #[ORM\OneToMany(targetEntity: Notifications::class, mappedBy: 'users_id')]
    private Collection $notifications;

    /**
     * @var Collection<int, Habits>
     */
    #[ORM\OneToMany(targetEntity: Habits::class, mappedBy: 'users_id')]
    private Collection $habits;

    /**
     * @var Collection<int, Badges>
     */
    #[ORM\ManyToMany(targetEntity: Badges::class, mappedBy: 'users_id')]
    private Collection $badges;

    /**
     * @var Collection<int, DefiUsers>
     */
    #[ORM\OneToMany(targetEntity: DefiUsers::class, mappedBy: 'users_id')]
    private Collection $defiUsers;

    #[ORM\Column(type: Types::ARRAY)]
    private array $roles = [];

    /**
     * @var Collection<int, RefreshToken>
     */
    #[ORM\OneToMany(targetEntity: RefreshToken::class, mappedBy: 'user_id')]
    private Collection $refreshTokens;

    /**
     * @var Collection<int, DefiProgress>
     */
    #[ORM\OneToMany(targetEntity: DefiProgress::class, mappedBy: 'user_id')]
    private Collection $defiProgress;

    public function __construct()
    {
        $this->objectives = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->habits = new ArrayCollection();
        $this->badges = new ArrayCollection();
        $this->defiUsers = new ArrayCollection();
        $this->refreshTokens = new ArrayCollection();
        $this->defiProgress = new ArrayCollection();
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection<int, Objectives>
     */
    public function getObjectives(): Collection
    {
        return $this->objectives;
    }

    public function addObjective(Objectives $objective): static
    {
        if (!$this->objectives->contains($objective)) {
            $this->objectives->add($objective);
            $objective->setUsersId($this);
        }

        return $this;
    }

    public function removeObjective(Objectives $objective): static
    {
        if ($this->objectives->removeElement($objective)) {
            // set the owning side to null (unless already changed)
            if ($objective->getUsersId() === $this) {
                $objective->setUsersId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notifications>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notifications $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUsersId($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUsersId() === $this) {
                $notification->setUsersId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Habits>
     */
    public function getHabits(): Collection
    {
        return $this->habits;
    }

    public function addHabit(Habits $habit): static
    {
        if (!$this->habits->contains($habit)) {
            $this->habits->add($habit);
            $habit->setUsersId($this);
        }

        return $this;
    }

    public function removeHabit(Habits $habit): static
    {
        if ($this->habits->removeElement($habit)) {
            // set the owning side to null (unless already changed)
            if ($habit->getUsersId() === $this) {
                $habit->setUsersId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Badges>
     */
    public function getBadges(): Collection
    {
        return $this->badges;
    }

    public function addBadge(Badges $badge): static
    {
        if (!$this->badges->contains($badge)) {
            $this->badges->add($badge);
            $badge->addUsersId($this);
        }

        return $this;
    }

    public function removeBadge(Badges $badge): static
    {
        if ($this->badges->removeElement($badge)) {
            $badge->removeUsersId($this);
        }

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
            $defiUser->setUsersId($this);
        }

        return $this;
    }

    public function removeDefiUser(DefiUsers $defiUser): static
    {
        if ($this->defiUsers->removeElement($defiUser)) {
            // set the owning side to null (unless already changed)
            if ($defiUser->getUsersId() === $this) {
                $defiUser->setUsersId(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, RefreshToken>
     */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    public function addRefreshToken(RefreshToken $refreshToken): static
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->add($refreshToken);
            $refreshToken->setUserId($this);
        }

        return $this;
    }

    public function removeRefreshToken(RefreshToken $refreshToken): static
    {
        if ($this->refreshTokens->removeElement($refreshToken)) {
            // set the owning side to null (unless already changed)
            if ($refreshToken->getUserId() === $this) {
                $refreshToken->setUserId(null);
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

    public function addDefi(DefiProgress $defiProgress): static
    {
        if (!$this->defiProgress->contains($defiProgress)) {
            $this->defiProgress->add($defiProgress);
            $defiProgress->setUserId($this);
        }

        return $this;
    }

    public function removeDefi(DefiProgress $defiProgress): static
    {
        if ($this->defiProgress->removeElement($defiProgress)) {
            // set the owning side to null (unless already changed)
            if ($defiProgress->getUserId() === $this) {
                $defiProgress->setUserId(null);
            }
        }

        return $this;
    }
}
