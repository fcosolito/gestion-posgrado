<?php

namespace App\Entity;

use App\Repository\CarreraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarreraRepository::class)]
class Carrera
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column]
    private ?int $nroOrdenanza = null;

    #[ORM\Column]
    private ?int $nroImplementacion = null;

    #[ORM\Column(nullable: true)]
    private ?int $cantidadCuotas = null;

    #[ORM\Column(nullable: true)]
    private ?float $precioInscripcion = null;

    /**
     * @var Collection<int, PrecioCarrera>
     */
    #[ORM\OneToMany(targetEntity: PrecioCarrera::class, mappedBy: 'carrera', orphanRemoval: true)]
    private Collection $precios;

    public function __construct()
    {
        $this->precios = new ArrayCollection();
    }

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

    public function getCantidadCuotas(): ?int
    {
        return $this->cantidadCuotas;
    }

    public function setCantidadCuotas(?int $cantidadCuotas): static
    {
        $this->cantidadCuotas = $cantidadCuotas;

        return $this;
    }

    public function getPrecioInscripcion(): ?float
    {
        return $this->precioInscripcion;
    }

    public function setPrecioInscripcion(?float $precioInscripcion): static
    {
        $this->precioInscripcion = $precioInscripcion;

        return $this;
    }

    /**
     * @return Collection<int, PrecioCarrera>
     */
    public function getPrecios(): Collection
    {
        return $this->precios;
    }

    public function addPrecio(PrecioCarrera $precio): static
    {
        if (!$this->precios->contains($precio)) {
            $this->precios->add($precio);
            $precio->setCarrera($this);
        }

        return $this;
    }

    public function removePrecio(PrecioCarrera $precio): static
    {
        if ($this->precios->removeElement($precio)) {
            // set the owning side to null (unless already changed)
            if ($precio->getCarrera() === $this) {
                $precio->setCarrera(null);
            }
        }

        return $this;
    }
}
