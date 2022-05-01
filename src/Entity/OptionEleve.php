<?php

namespace App\Entity;

use App\Repository\OptionEleveRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OptionEleveRepository::class)
 */
class OptionEleve
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Option::class, inversedBy="eleve")
     */
    private $optionEleve;

    /**
     * @ORM\ManyToOne(targetEntity=Eleve::class, inversedBy="optionEleves")
     */
    private $eleve;

    /**
     * @ORM\Column(type="datetime")
     */
    private $valide;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOptionEleve(): ?Option
    {
        return $this->optionEleve;
    }

    public function setOptionEleve(?Option $optionEleve): self
    {
        $this->optionEleve = $optionEleve;

        return $this;
    }

    public function getEleve(): ?Eleve
    {
        return $this->eleve;
    }

    public function setEleve(?Eleve $eleve): self
    {
        $this->eleve = $eleve;

        return $this;
    }

    public function getValide(): ?\DateTimeInterface
    {
        return $this->valide;
    }

    public function setValide(\DateTimeInterface $valide): self
    {
        $this->valide = $valide;

        return $this;
    }
}
