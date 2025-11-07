<?php

namespace App\Entity;

use App\Repository\InscripcionEdicionRepository;
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

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Nota $nota = null;

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

    public function getNota(): ?Nota
    {
        return $this->nota;
    }

    public function setNota(?Nota $nota): static
    {
        $this->nota = $nota;

        return $this;
    }
}
