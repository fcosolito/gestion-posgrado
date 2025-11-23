<?php

namespace App\Entity;

use App\Repository\PagoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity(repositoryClass: PagoRepository::class)]
class Pago
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $monto= null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fechaPago = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Comprobante $comprobante = null;

    #[ORM\OneToMany(mappedBy: 'pago', targetEntity: PagoCuota::class, cascade: ['persist', 'remove'])]
    private Collection $pagoCuotas;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonto(): ?float
    {
        return $this->monto;
    }

    public function setMonto(float $monto): static
    {
        $this->monto = $monto;

        return $this;
    }

    public function getFechaPago(): ?\DateTime
    {
        return $this->fechaPago;
    }

    public function setFechaPago(\DateTime $fechaPago): static
    {
        $this->fechaPago = $fechaPago;

        return $this;
    }

    public function getComprobante(): ?Comprobante
    {
        return $this->comprobante;
    }

    public function setComprobante(?Comprobante $comprobante): static
    {
        $this->comprobante = $comprobante;

        return $this;
    }

    public function getPagoCuotas(): Collection
    {
        return $this->pagoCuotas;
    }

    public function addPagoCuota(PagoCuota $pagoCuota): static
    {
        if (!$this->pagoCuotas->contains($pagoCuota)) {
            $this->pagoCuotas->add($pagoCuota);
            $pagoCuota->setPago($this);
        }

        return $this;
    }

    public function removePagoCuota(PagoCuota $pagoCuota): static
    {
        if ($this->pagoCuotas->removeElement($pagoCuota)) {
            if ($pagoCuota->getPago() === $this) {
                $pagoCuota->setPago(null);
            }
        }

        return $this;
    }
}
