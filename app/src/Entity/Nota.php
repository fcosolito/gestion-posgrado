<?php

namespace App\Entity;

use App\Repository\NotaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotaRepository::class)]
class Nota
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $valor = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fechaCarga = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?InscripcionEdicion $inscripcionEdicion = null;


    #[ORM\OneToOne(targetEntity: DocumentacionNota::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?DocumentacionNota $documentacionNota = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValor(): ?float
    {
        return $this->valor;
    }

    public function setValor(float $valor): static
    {
        $this->valor = $valor;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getFechaCarga(): ?\DateTime
    {
        return $this->fechaCarga;
    }

    public function setFechaCarga(\DateTime $fechaCarga): static
    {
        $this->fechaCarga = $fechaCarga;

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

    public function getDocumentacionNota(): ?DocumentacionNota
    {
        return $this->documentacionNota;
    }

    public function setDocumentacionNota(?DocumentacionNota $documentacionNota): static
    {
        $this->documentacionNota = $documentacionNota;

        return $this;
    }
}
