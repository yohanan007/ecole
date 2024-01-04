<?php

namespace App\Entity;

use App\Repository\EleveMatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EleveMatiereRepository::class)
 */
class EleveMatiere
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Matiere::class, mappedBy="eleveMatiere")
     */
    private $matiere;

    /**
     * @ORM\OneToMany(targetEntity=Eleve::class, mappedBy="eleveMatiere")
     */
    private $Eleve;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $principale;

    public function __construct()
    {
        $this->matiere = new ArrayCollection();
        $this->Eleve = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Matiere>
     */
    public function getMatiere(): Collection
    {
        return $this->matiere;
    }

    public function addMatiere(Matiere $matiere): self
    {
        if (!$this->matiere->contains($matiere)) {
            $this->matiere[] = $matiere;
            $matiere->setEleveMatiere($this);
        }

        return $this;
    }

    public function removeMatiere(Matiere $matiere): self
    {
        if ($this->matiere->removeElement($matiere)) {
            // set the owning side to null (unless already changed)
            if ($matiere->getEleveMatiere() === $this) {
                $matiere->setEleveMatiere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Eleve>
     */
    public function getEleve(): Collection
    {
        return $this->Eleve;
    }

    public function addEleve(Eleve $eleve): self
    {
        if (!$this->Eleve->contains($eleve)) {
            $this->Eleve[] = $eleve;
            $eleve->setEleveMatiere($this);
        }

        return $this;
    }

    public function removeEleve(Eleve $eleve): self
    {
        if ($this->Eleve->removeElement($eleve)) {
            // set the owning side to null (unless already changed)
            if ($eleve->getEleveMatiere() === $this) {
                $eleve->setEleveMatiere(null);
            }
        }

        return $this;
    }

    public function getPrincipale(): ?\DateTimeInterface
    {
        return $this->principale;
    }

    public function setPrincipale(?\DateTimeInterface $principale): self
    {
        $this->principale = $principale;

        return $this;
    }
}
