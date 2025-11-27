<?php

namespace App\Entity;

use App\Repository\InscripcionEdicionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscripcionEdicionRepository::class)]
class InscripcionEdicion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Descuento $descuento = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Alumno $alumno = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Edicion $edicion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $fechaInscripcion = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $nroLegajo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescuento(): ?Descuento
    {
        return $this->descuento;
    }

    public function setDescuento(?Descuento $descuento): static
    {
        $this->descuento = $descuento;

        return $this;
    }

    public function getAlumno(): ?Alumno
    {
        return $this->alumno;
    }

    public function setAlumno(?Alumno $alumno): static
    {
        $this->alumno = $alumno;

        return $this;
    }

    public function getEdicion(): ?Edicion
    {
        return $this->edicion;
    }

    public function setEdicion(?Edicion $edicion): static
    {
        $this->edicion = $edicion;

        return $this;
    }

    public function getFechaInscripcion(): ?\DateTime
    {
        return $this->fechaInscripcion;
    }

    public function setFechaInscripcion(?\DateTime $fechaInscripcion): static
    {
        $this->fechaInscripcion = $fechaInscripcion;

        return $this;
    }

    public function getNroLegajo(): ?string
    {
        return $this->nroLegajo;
    }

    public function setNroLegajo(?string $nroLegajo): static
    {
        $this->nroLegajo = $nroLegajo;

        return $this;
    }
}
