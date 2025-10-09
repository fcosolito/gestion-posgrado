<?php

namespace App\Entity;

use App\Repository\CursoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CursoRepository::class)]
class Curso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column]
    private ?int $horas = null;

    #[ORM\Column]
    private ?int $nroOrdenanza = null;

    #[ORM\Column]
    private ?int $nroImplementacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getHoras(): ?int
    {
        return $this->horas;
    }

    public function setHoras(int $horas): static
    {
        $this->horas = $horas;

        return $this;
    }

    public function getNroOrdenanza(): ?int
    {
        return $this->nroOrdenanza;
    }

    public function setNroOrdenanza(int $nroOrdenanza): static
    {
        $this->nroOrdenanza = $nroOrdenanza;

        return $this;
    }

    public function getNroImplementacion(): ?int
    {
        return $this->nroImplementacion;
    }

    public function setNroImplementacion(int $nroImplementacion): static
    {
        $this->nroImplementacion = $nroImplementacion;

        return $this;
    }
}
