<?php

namespace App\Entity;

use App\Repository\EleveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Annotation\Groups;
#[ORM\Entity(repositoryClass: EleveRepository::class)]
#[ApiResource(operations: [
    new Get()],
    routePrefix: "/eleve")]
class Eleve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    /**
     * Un utilisateur peut être un élève.
     */
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    private $user;

    #[Groups(["list_eleve"])]
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $nom;

    #[Groups(["list_eleve"])]
    #[ORM\Column(type: "string", length: 255)]
    private $prenom;

    #[ORM\Column(type: "string", length: 255)]
    private $adresse;

    #[ORM\Column(type: "string", length: 255)]
    private $telephone;

    #[ORM\ManyToMany(targetEntity: ParentEleve::class, mappedBy: "enfant")]
    private $parentEleves;

    #[ORM\OneToMany(targetEntity: OptionEleve::class, mappedBy: "eleve")]
    private $optionEleves;

    #[ORM\ManyToOne(targetEntity: EleveMatiere::class, inversedBy: "Eleve")]
    private $eleveMatiere;

    #[ORM\OneToMany(targetEntity: ClasseEleve::class, mappedBy: "Eleve")]
    private $classeEleves;

    /**
     * Validation de l'inscription par un admin
     */
    #[ORM\Column(type: "boolean")]
    private $isValidated = false;

    /**
     * Admin qui a validé l'élève
     */
    #[ORM\ManyToOne(targetEntity: Admin::class)]
    #[ORM\JoinColumn(name: "validated_by_admin_id", referencedColumnName: "id", nullable: true)]
    private $validatedByAdmin;

    /**
     * Date de validation
     */
    #[ORM\Column(type: "datetime", nullable: true)]
    private $validatedAt;

    public function __construct()
    {
        $this->parentEleves = new ArrayCollection();
        $this->optionEleves = new ArrayCollection();
        $this->classeEleves = new ArrayCollection();
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
    
    #[Groups(["list_eleve"])]
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection<int, ParentEleve>
     */
    public function getParentEleves(): Collection
    {
        return $this->parentEleves;
    }

    public function addParentElefe(ParentEleve $parentElefe): self
    {
        if (!$this->parentEleves->contains($parentElefe)) {
            $this->parentEleves[] = $parentElefe;
            $parentElefe->addEnfant($this);
        }

        return $this;
    }

    public function removeParentElefe(ParentEleve $parentElefe): self
    {
        if ($this->parentEleves->removeElement($parentElefe)) {
            $parentElefe->removeEnfant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, OptionEleve>
     */
    public function getOptionEleves(): Collection
    {
        return $this->optionEleves;
    }

    public function addOptionElefe(OptionEleve $optionElefe): self
    {
        if (!$this->optionEleves->contains($optionElefe)) {
            $this->optionEleves[] = $optionElefe;
            $optionElefe->setEleve($this);
        }

        return $this;
    }

    public function removeOptionElefe(OptionEleve $optionElefe): self
    {
        if ($this->optionEleves->removeElement($optionElefe)) {
            // set the owning side to null (unless already changed)
            if ($optionElefe->getEleve() === $this) {
                $optionElefe->setEleve(null);
            }
        }

        return $this;
    }

    public function getEleveMatiere(): ?EleveMatiere
    {
        return $this->eleveMatiere;
    }

    public function setEleveMatiere(?EleveMatiere $eleveMatiere): self
    {
        $this->eleveMatiere = $eleveMatiere;

        return $this;
    }

    /**
     * @return Collection<int, ClasseEleve>
     */
    public function getClasseEleves(): Collection
    {
        return $this->classeEleves;
    }

    public function addClasseElefe(ClasseEleve $classeElefe): self
    {
        if (!$this->classeEleves->contains($classeElefe)) {
            $this->classeEleves[] = $classeElefe;
            $classeElefe->setEleve($this);
        }

        return $this;
    }

    public function removeClasseElefe(ClasseEleve $classeElefe): self
    {
        if ($this->classeEleves->removeElement($classeElefe)) {
            // set the owning side to null (unless already changed)
            if ($classeElefe->getEleve() === $this) {
                $classeElefe->setEleve(null);
            }
        }

        return $this;
    }

    public function isValidated(): bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): self
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getValidatedByAdmin(): ?Admin
    {
        return $this->validatedByAdmin;
    }

    public function setValidatedByAdmin(?Admin $validatedByAdmin): self
    {
        $this->validatedByAdmin = $validatedByAdmin;
        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;
        return $this;
    }

    public function __toString()
    {
        return $this->nom . " " . $this->prenom;
    }
}
