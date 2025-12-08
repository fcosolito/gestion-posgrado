<?php

namespace App\Entity;

use App\Repository\CuotaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CuotaRepository::class)]
class Cuota
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $numeroCuota = null;

    #[ORM\ManyToOne]
    private ?InscripcionCarrera $inscripcionCarrera = null;

    #[ORM\ManyToOne]
    private ?InscripcionEdicion $inscripcionEdicion = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCuota(): ?int
    {
        return $this->numeroCuota;
    }

    public function setNumeroCuota(int $numeroCuota): static
    {
        $this->numeroCuota = $numeroCuota;

        return $this;
    }

    public function getInscripcionCarrera(): ?InscripcionCarrera
    {
        return $this->inscripcionCarrera;
    }

    public function setInscripcionCarrera(?InscripcionCarrera $inscripcionCarrera): static
    {
        $this->inscripcionCarrera = $inscripcionCarrera;

        return $this;
    }

    public function getInscripcionEdicion(): ?InscripcionEdicion
    {
        return $this->inscripcionEdicion;
    }

    public function setInscripcionEdicion(?InscripcionEdicion $inscripcionEdicion): static
    {
        $this->inscripcionEdicion = $inscripcionEdicion;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }
}
