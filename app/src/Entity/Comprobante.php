<?php

namespace App\Entity;

use App\Repository\ComprobanteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComprobanteRepository::class)]
class Comprobante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $archivo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArchivo(): ?string
    {
        return $this->archivo;
    }

    public function setArchivo(string $archivo): static
    {
        $this->archivo = $archivo;

        return $this;
    }
}
