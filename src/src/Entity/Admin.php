<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdminRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    /**
     * Relation OneToOne : Un admin est lié à un User pour l'authentification
     */
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'string', length: 255)]
    private $prenom;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $telephone;

    /**
     * Les admins peuvent gérer les RDV (Evenement)
     */
    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: "adminCreateur")]
    private Collection $evenementsCreated;

    /**
     * Status de l'admin
     */
    #[ORM\Column(type: 'boolean')]
    private $isActive = true;

    public function __construct()
    {
        $this->evenementsCreated = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
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

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenementsCreated(): Collection
    {
        return $this->evenementsCreated;
    }

    public function addEvenementCreated(Evenement $evenement): self
    {
        if (!$this->evenementsCreated->contains($evenement)) {
            $this->evenementsCreated[] = $evenement;
            $evenement->setAdminCreateur($this);
        }
        return $this;
    }

    public function removeEvenementCreated(Evenement $evenement): self
    {
        if ($this->evenementsCreated->removeElement($evenement)) {
            if ($evenement->getAdminCreateur() === $this) {
                $evenement->setAdminCreateur(null);
            }
        }
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function __toString(): string
    {
        return $this->prenom . ' ' . $this->nom . ' (' . $this->user->getEmail() . ')';
    }
}