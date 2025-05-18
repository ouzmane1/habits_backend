<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
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

    public function __construct()
    {
        $this->objectives = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->habits = new ArrayCollection();
        $this->badges = new ArrayCollection();
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
}
