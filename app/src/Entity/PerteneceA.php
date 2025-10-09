<?php

namespace App\Entity;

use App\Repository\PerteneceARepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PerteneceARepository::class)]
class PerteneceA
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $esElectivo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Carrera $carrera = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Curso $curso = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEsElectivo(): ?bool
    {
        return $this->esElectivo;
    }

    public function setEsElectivo(bool $esElectivo): static
    {
        $this->esElectivo = $esElectivo;

        return $this;
    }

    public function getCarrera(): ?Carrera
    {
        return $this->carrera;
    }

    public function setCarrera(?Carrera $carrera): static
    {
        $this->carrera = $carrera;

        return $this;
    }

    public function getCurso(): ?Curso
    {
        return $this->curso;
    }

    public function setCurso(?Curso $curso): static
    {
        $this->curso = $curso;

        return $this;
    }
}
