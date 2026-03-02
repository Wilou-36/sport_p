<?php

namespace App\Entity;

use App\Repository\CoachRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Sport;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CoachRepository::class)]
class Coach
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $specialite = null;

    #[ORM\Column(length: 100)]
    private ?string $experience = null;

    #[ORM\OneToOne(inversedBy: "coach", cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;
    
    #[ORM\ManyToOne(targetEntity: Sport::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sport $sport = null;
    
    #[ORM\ManyToMany(mappedBy: "coachs", targetEntity: Client::class)]
    private Collection $clients;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): self
    {
        $this->specialite = $specialite;
        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(string $experience): self
    {
        $this->experience = $experience;
        return $this;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
    public function getSport(): ?Sport
    {
        return $this->sport;
    }

    public function setSport(?Sport $sport): static
    {
        $this->sport = $sport;
        return $this;
    }
    
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getClients(): Collection
    {
        return $this->clients;
    }
}
