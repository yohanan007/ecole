<?php

namespace App\Entity;

use App\Repository\OptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OptionRepository::class)
 * @ORM\Table(name="`option`")
 */
class Option
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=OptionEleve::class, mappedBy="optionEleve")
     */
    private $eleve;

    public function __construct()
    {
        $this->eleve = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, OptionEleve>
     */
    public function getEleve(): Collection
    {
        return $this->eleve;
    }

    public function addEleve(OptionEleve $eleve): self
    {
        if (!$this->eleve->contains($eleve)) {
            $this->eleve[] = $eleve;
            $eleve->setOptionEleve($this);
        }

        return $this;
    }

    public function removeEleve(OptionEleve $eleve): self
    {
        if ($this->eleve->removeElement($eleve)) {
            // set the owning side to null (unless already changed)
            if ($eleve->getOptionEleve() === $this) {
                $eleve->setOptionEleve(null);
            }
        }

        return $this;
    }
}
