<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $sujet = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $corps = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: "evenements")]
    private Collection $users;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $duree = null; // en minutes par ex.

    #[ORM\Column(type:"string", length: 50, nullable:true)]
    private ?string $recurrence = null;

    // ✅ Parent-Child Pattern
    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: "occurrences")]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?Evenement $parent = null;

    #[ORM\OneToMany(mappedBy: "parent", targetEntity: Evenement::class, orphanRemoval: true, cascade: ["persist", "remove"])]
    #[ORM\OrderBy(["heureDebut" => "ASC"])]
    private Collection $occurrences;

    // ✅ Date de début (remplace Agenda.heureDebut)
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $heureDebut = null;

    // ✅ Date de fin de récurrence (optionnel)
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $recurrenceEnd = null;

    #[ORM\ManyToOne(targetEntity: Agenda::class, inversedBy: "evenements")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Agenda $agenda = null;

    /**
     * Admin qui a créé cet RDV
     */
    #[ORM\ManyToOne(targetEntity: Admin::class, inversedBy: "evenementsCreated")]
    #[ORM\JoinColumn(name: "admin_createur_id", referencedColumnName: "id", nullable: true)]
    private ?Admin $adminCreateur = null;

    /**
     * Date de création du RDV
     */
    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->occurrences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(?string $sujet): self
    {
        $this->sujet = $sujet;

        return $this;
    }

    public function getCorps(): ?string
    {
        return $this->corps;
    }

    public function setCorps(?string $corps): self
    {
        $this->corps = $corps;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getRecurrence(): ?string
    {
        return $this->recurrence;
    }

    public function setRecurrence(?string $recurrence): self
    {
        $this->recurrence = $recurrence;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getAgenda(): ?Agenda
    {
        return $this->agenda;
    }

    public function setAgenda(?Agenda $agenda): self
    {
        $this->agenda = $agenda;

        return $this;
    }

    public function getAdminCreateur(): ?Admin
    {
        return $this->adminCreateur;
    }

    public function setAdminCreateur(?Admin $adminCreateur): self
    {
        $this->adminCreateur = $adminCreateur;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // ✅ PARENT-CHILD PATTERN METHODS

    public function getParent(): ?Evenement
    {
        return $this->parent;
    }

    public function setParent(?Evenement $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function isParent(): bool
    {
        return $this->parent === null && !empty($this->recurrence) && $this->recurrence !== 'aucune';
    }

    public function isOccurrence(): bool
    {
        return $this->parent !== null;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getOccurrences(): Collection
    {
        return $this->occurrences;
    }

    public function addOccurrence(Evenement $occurrence): self
    {
        if (!$this->occurrences->contains($occurrence)) {
            $this->occurrences[] = $occurrence;
            $occurrence->setParent($this);
        }
        return $this;
    }

    public function removeOccurrence(Evenement $occurrence): self
    {
        if ($this->occurrences->removeElement($occurrence)) {
            if ($occurrence->getParent() === $this) {
                $occurrence->setParent(null);
            }
        }
        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(?\DateTimeInterface $heureDebut): self
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getRecurrenceEnd(): ?\DateTimeInterface
    {
        return $this->recurrenceEnd;
    }

    public function setRecurrenceEnd(?\DateTimeInterface $recurrenceEnd): self
    {
        $this->recurrenceEnd = $recurrenceEnd;
        return $this;
    }
}
