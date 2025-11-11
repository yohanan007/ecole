<?php

namespace App\Entity;

use App\Repository\ClasseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;

#[ORM\Entity(repositoryClass: ClasseRepository::class)]
#[ApiResource(operations: [
    new Get(routePrefix: "/classe")]
    )]
class Classe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255)]
    private $nom;

    #[ORM\ManyToOne(targetEntity: Niveau::class, inversedBy: "classe")]
    #[Groups(["list_class"])]
    private $niveau;

    #[ORM\OneToMany(targetEntity: ClasseEleve::class, mappedBy: "classe")]
    private $classeEleves;

    public function __construct()
    {
        $this->classeEleves = new ArrayCollection();
    }

    #[Groups(["list_class"])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(["list_class"])]
    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }
    
    #[Groups(["list_class"])]
    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;

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
            $classeElefe->setClasse($this);
        }

        return $this;
    }

    public function removeClasseElefe(ClasseEleve $classeElefe): self
    {
        if ($this->classeEleves->removeElement($classeElefe)) {
            // set the owning side to null (unless already changed)
            if ($classeElefe->getClasse() === $this) {
                $classeElefe->setClasse(null);
            }
        }

        return $this;
    }
}
