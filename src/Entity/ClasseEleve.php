<?php

namespace App\Entity;

use App\Repository\ClasseEleveRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClasseEleveRepository::class)
 */
class ClasseEleve
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="classeEleves")
     */
    private $classe;

    /**
     * @ORM\ManyToOne(targetEntity=Eleve::class, inversedBy="classeEleves")
     */
    private $Eleve;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $DateValide;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $DateFin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    public function getEleve(): ?Eleve
    {
        return $this->Eleve;
    }

    public function setEleve(?Eleve $Eleve): self
    {
        $this->Eleve = $Eleve;

        return $this;
    }

    public function getDateValide(): ?\DateTimeInterface
    {
        return $this->DateValide;
    }

    public function setDateValide(\DateTimeInterface $DateValide): self
    {
        $this->DateValide = $DateValide;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->DateFin;
    }

    public function setDateFin(\DateTimeInterface $DateFin): self
    {
        $this->DateFin = $DateFin;

        return $this;
    }


    public function __toString(): string
    {
        return strval($this->id);
    }
}
