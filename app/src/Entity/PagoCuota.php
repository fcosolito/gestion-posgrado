<?php

namespace App\Entity;

use App\Repository\PagoCuotaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PagoCuotaRepository::class)]
class PagoCuota
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cuota $cuota = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pago $pago = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCuota(): ?Cuota
    {
        return $this->cuota;
    }

    public function setCuota(?Cuota $cuota): static
    {
        $this->cuota = $cuota;

        return $this;
    }

    public function getPago(): ?Pago
    {
        return $this->pago;
    }

    public function setPago(?Pago $pago): static
    {
        $this->pago = $pago;

        return $this;
    }
}
