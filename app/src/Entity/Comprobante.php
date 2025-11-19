<?php

namespace App\Entity;

use App\Repository\ComprobanteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ComprobanteRepository::class)]
#[Vich\Uploadable]
class Comprobante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Vich\UploadableField(mapping: 'comprobantes', fileNameProperty: 'archivo', size: 'tamano', mimeType: 'mimeType')]
    private ?File $archivoFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $archivo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    private ?int $tamano = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $fechaActualizacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setArchivoFile(?File $archivo = null): void
    {
        $this->archivoFile = $archivo;

        if (null !== $archivo) {
            $this->fechaActualizacion = new \DateTimeImmutable();
        }
    }

    public function getArchivoFile(): ?File
    {
        return $this->archivoFile;
    }

    public function getArchivo(): ?string
    {
        return $this->archivo;
    }

    public function setArchivo(?string $archivo): static
    {
        $this->archivo = $archivo;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getTamano(): ?int
    {
        return $this->tamano;
    }

    public function setTamano(?int $tamano): static
    {
        $this->tamano = $tamano;

        return $this;
    }

    public function getFechaActualizacion(): ?\DateTimeInterface
    {
        return $this->fechaActualizacion;
    }

    public function setFechaActualizacion(?\DateTimeInterface $fechaActualizacion): static
    {
        $this->fechaActualizacion = $fechaActualizacion;

        return $this;
    }

    public function __toString(): string
    {
        return $this->archivo ?? 'Sin archivo';
    }
}
