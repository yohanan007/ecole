<?php

namespace App\Entity;

use App\Repository\EleveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

/**
 * @ORM\Entity(repositoryClass=EleveRepository::class)
 */
class Eleve
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * un utilisateur peut être un eleve.
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telephone;


    /**
     * @ORM\ManyToMany(targetEntity=ParentEleve::class, mappedBy="enfant")
     */
    private $parentEleves;

    /**
     * @ORM\OneToMany(targetEntity=OptionEleve::class, mappedBy="eleve")
     */
    private $optionEleves;

    /**
     * @ORM\ManyToOne(targetEntity=EleveMatiere::class, inversedBy="Eleve")
     */
    private $eleveMatiere;

    /**
     * @ORM\OneToMany(targetEntity=ClasseEleve::class, mappedBy="Eleve")
     */
    private $classeEleves;

    public function __construct()
    {
        //parent::__construct();

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

    public function __toString()
    {
        return $this->nom." ".$this->prenom;
    }
}
