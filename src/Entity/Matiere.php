<?php

namespace App\Entity;

use App\Repository\MatiereRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MatiereRepository::class)
 */
class Matiere
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
     * @ORM\ManyToOne(targetEntity=EleveMatiere::class, inversedBy="matiere")
     */
    private $eleveMatiere;

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

    public function getEleveMatiere(): ?EleveMatiere
    {
        return $this->eleveMatiere;
    }

    public function setEleveMatiere(?EleveMatiere $eleveMatiere): self
    {
        $this->eleveMatiere = $eleveMatiere;

        return $this;
    }
}
