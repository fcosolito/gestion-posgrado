<?php

namespace App\Entity;

use App\Repository\PagoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PagoRepository::class)]
class Pago
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $montoPagado = null;

    #[ORM\Column]
    private ?float $montoCuota = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fechaPago = null;

    #[ORM\ManyToOne]
    private ?Comprobante $comprobante = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontoPagado(): ?float
    {
        return $this->montoPagado;
    }

    public function setMontoPagado(float $montoPagado): static
    {
        $this->montoPagado = $montoPagado;

        return $this;
    }

    public function getMontoCuota(): ?float
    {
        return $this->montoCuota;
    }

    public function setMontoCuota(float $montoCuota): static
    {
        $this->montoCuota = $montoCuota;

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
}
