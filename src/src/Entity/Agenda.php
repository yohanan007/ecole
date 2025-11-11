<?php

namespace App\Entity;

use App\Repository\AgendaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgendaRepository::class)]
class Agenda
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $heureFinReccurence = null;

    #[ORM\OneToMany(mappedBy: "agenda", targetEntity: Evenement::class, orphanRemoval: true)]
    private Collection $evenements;

    public function __construct()
    {
        $this->evenements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getHeureFinReccurence(): ?\DateTimeInterface
    {
        return $this->heureFinReccurence;
    }

    public function setHeureFinReccurence(?\DateTimeInterface $heureFinReccurence): self
    {
        $this->heureFinReccurence = $heureFinReccurence;

        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements[] = $evenement;
            $evenement->setAgenda($this); // ✅ cohérent avec ManyToOne
        }

        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        if ($this->evenements->removeElement($evenement)) {
            // mettre à null seulement si c’était lié à cet agenda
            if ($evenement->getAgenda() === $this) {
                $evenement->setAgenda(null);
            }
        }

        return $this;
    }
}
