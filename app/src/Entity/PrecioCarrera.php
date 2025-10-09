<?php

namespace App\Entity;

use App\Repository\PrecioCarreraRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrecioCarreraRepository::class)]
class PrecioCarrera
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $precio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fechaVigencia = null;

    #[ORM\Column]
    private ?\DateTime $fechaCreacion = null;

    #[ORM\ManyToOne(inversedBy: 'precios')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Carrera $carrera = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getFechaVigencia(): ?\DateTime
    {
        return $this->fechaVigencia;
    }

    public function setFechaVigencia(\DateTime $fechaVigencia): static
    {
        $this->fechaVigencia = $fechaVigencia;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTime
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTime $fechaCreacion): static
    {
        $this->fechaCreacion = $fechaCreacion;

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
}
