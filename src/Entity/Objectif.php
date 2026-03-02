<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: "objectif")]
#[ORM\HasLifecycleCallbacks]
class Objectif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\Column(name: "vo2_objectif", type: "float", nullable: true)]
    private ?float $vo2Objectif = null;

    #[ORM\Column(name: "charge_hebdo_objectif", type: "integer", nullable: true)]
    private ?int $chargeHebdoObjectif = null;

    #[ORM\Column(name: "masse_grasse_objectif", type: "float", nullable: true)]
    private ?float $masseGrasseObjectif = null;

    #[ORM\Column(name: "performance_objectif", type: "string", length: 255, nullable: true)]
    private ?string $performanceObjectif = null;

    #[ORM\Column(name: "competition_nom", type: "string", length: 255, nullable: true)]
    private ?string $competitionNom = null;

    #[ORM\Column(name: "competition_date", type: "date", nullable: true)]
    private ?\DateTimeInterface $competitionDate = null;

    #[ORM\Column(name: "date_debut", type: "date")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: "date_fin", type: "date")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(name: "macrocycle", type: "string", length: 100)]
    private ?string $macrocycle = null;

    #[ORM\Column(name: "mesocycle", type: "string", length: 100)]
    private ?string $mesocycle = null;

    #[ORM\Column(name: "created_at", type: "datetime")]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // =========================
    // GETTERS & SETTERS
    // =========================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getVo2Objectif(): ?float
    {
        return $this->vo2Objectif;
    }

    public function setVo2Objectif(?float $vo2Objectif): self
    {
        $this->vo2Objectif = $vo2Objectif;
        return $this;
    }

    public function getChargeHebdoObjectif(): ?int
    {
        return $this->chargeHebdoObjectif;
    }

    public function setChargeHebdoObjectif(?int $charge): self
    {
        $this->chargeHebdoObjectif = $charge;
        return $this;
    }

    public function getMasseGrasseObjectif(): ?float
    {
        return $this->masseGrasseObjectif;
    }

    public function setMasseGrasseObjectif(?float $masse): self
    {
        $this->masseGrasseObjectif = $masse;
        return $this;
    }

    public function getPerformanceObjectif(): ?string
    {
        return $this->performanceObjectif;
    }

    public function setPerformanceObjectif(?string $performance): self
    {
        $this->performanceObjectif = $performance;
        return $this;
    }

    public function getCompetitionNom(): ?string
    {
        return $this->competitionNom;
    }

    public function setCompetitionNom(?string $nom): self
    {
        $this->competitionNom = $nom;
        return $this;
    }

    public function getCompetitionDate(): ?\DateTimeInterface
    {
        return $this->competitionDate;
    }

    public function setCompetitionDate(?\DateTimeInterface $date): self
    {
        $this->competitionDate = $date;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $date): self
    {
        $this->dateDebut = $date;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $date): self
    {
        $this->dateFin = $date;
        return $this;
    }

    public function getMacrocycle(): ?string
    {
        return $this->macrocycle;
    }

    public function setMacrocycle(string $macro): self
    {
        $this->macrocycle = $macro;
        return $this;
    }

    public function getMesocycle(): ?string
    {
        return $this->mesocycle;
    }

    public function setMesocycle(string $meso): self
    {
        $this->mesocycle = $meso;
        return $this;
    }

    public function getStatut(): string
    {
        $today = new \DateTime();

        if ($today < $this->dateDebut) {
            return 'Futur';
        }

        if ($today > $this->dateFin) {
            return 'Terminé';
        }

        return 'Actif';
    }
}