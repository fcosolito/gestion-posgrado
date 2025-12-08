<?php

namespace App\Service;

use App\Entity\Comprobante;
use App\Entity\Cuota;
use App\Entity\Pago;
use App\Entity\PagoCuota;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Filesystem\Filesystem;

class PagoService
{
    public function __construct(private EntityManagerInterface $em, private CuotaService $cuotaService) {}

    public function newComprobante(Comprobante $comprobante, $archivo, $comprobanteDir): array
    {
        try {
            $originalFilename = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$archivo->guessExtension();
            
            $archivo->move(
                $comprobanteDir,
                $newFilename
            );
            
            $comprobante->setArchivo($newFilename);

            $this->em->persist($comprobante);
            $this->em->flush();

            return ["estado" => "exito", "comprobante" => $comprobante];
        } catch (Exception $e) {
            return ["estado" => "error", "exception" => $e->getMessage()];
        }
    }
    
    public function updateComprobante(Comprobante $comprobante, $archivo, $archivoAntiguo, $comprobanteDir, Filesystem $filesystem): array
    {
        // Igual a newComprobante pero elimina el comprobante viejo
        try {
            $comprobante = $comprobante ?? new Comprobante();
            
            if ($archivoAntiguo) {
                // Eliminar el archivo antiguo si existe
                if ($archivoAntiguo && $filesystem->exists($comprobanteDir . '/' . $archivoAntiguo)) {
                    $filesystem->remove($comprobanteDir . '/' . $archivoAntiguo);
                }
            }
        } catch (Exception $e) {
            return ["estado" => "error", "exception" => $e->getMessage()];
        }
        return $this->newComprobante($comprobante, $archivo, $comprobanteDir);
    }

    public function new(Pago $pago, array $cuotasSeleccionadas): array
    {
        try {
            if (empty($cuotasSeleccionadas)) {
                return ["estado" => "error", "exception" => "No se seleccionó ninguna cuota."];
            }

            // 5. Calcular montos
            $numeroCuotas = count($cuotasSeleccionadas);
            $montoPorCuota = $pago->getMonto() / $numeroCuotas;

            // 6. Crear relaciones PagoCuota
            foreach ($cuotasSeleccionadas as $cuota) {
                $pagoCuota = new PagoCuota();
                $pagoCuota->setCuota($cuota);
                $pagoCuota->setPago($pago);
                $pagoCuota->setMontoCuota($montoPorCuota);
                $this->em->persist($pagoCuota);
            }

            // 7. Persistir todo
            $this->em->persist($pago);
            $this->em->flush();

            return ["estado" => "exito", "pago" => $pago];
        } catch (Exception $e) {
            return ["estado" => "error", "exception" => $e->getMessage()];
        }
    }

    public function asociarCuota(Pago $pago, Cuota $cuota, float $montoCuota): array
    {
        $pagoCuotaRepository = $this->em->getRepository(PagoCuota::class);
        $pagoCuota = $pagoCuotaRepository->findOneBy(["pago" => $pago, "cuota" => $cuota]) ?? new PagoCuota();

        $pagoCuota->setCuota($cuota);
        $pagoCuota->setPago($pago);

        // si el monto es 0 o negativo, usar el monto del pago
        if ($montoCuota > 0) {
            if ($montoCuota > $pago->getMonto()) {
                return ["estado" => "error", "exception" => "El monto no puede ser mayor al del pago."];
            } 
            $pagoCuota->setMontoCuota($montoCuota);
        } else {
            $pagoCuota->setMontoCuota($pago->getMonto());
        }

        $this->em->persist($pagoCuota);
        $this->em->flush();

        $cuota->setEstado($this->cuotaService->calcularEstado($cuota));
        $this->em->persist($cuota);
        $this->em->flush();

        return ["estado" => "exito"];
    }

    public function desasociarCuota(Pago $pago, Cuota $cuota): array
    {
        try {
            $pagoCuotaRepository = $this->em->getRepository(PagoCuota::class);
            $pagoCuota = $pagoCuotaRepository->findOneBy(["pago" => $pago, "cuota" => $cuota]) ?? new PagoCuota();

            $this->em->remove($pagoCuota);
            $this->em->flush();

            $cuota->setEstado($this->cuotaService->calcularEstado($cuota));
            $this->em->persist($cuota);
            $this->em->flush();
        } catch (Exception $e) {
            return ["estado" => "error", "exception" => $e->getMessage()];
        }
        return ["estado" => "exito"];
    }

    public function delete(Pago $pago, $comprobanteDir): array
    {
        try {
            $filesystem = new Filesystem();
            
            // 1. Eliminar el archivo físico del comprobante si existe
            $comprobante = $pago->getComprobante();
            if ($comprobante) {
                $archivoPath = $comprobanteDir . '/' . $comprobante->getArchivo();
                
                // Eliminar archivo físico de forma segura
                if ($filesystem->exists($archivoPath)) {
                    $filesystem->remove($archivoPath);
                }
                
                // Eliminar la entidad Comprobante
                $this->em->remove($comprobante);
            }
            
            $cuotas = [];
            // 2. Eliminar las relaciones PagoCuota
            foreach ($pago->getPagoCuotas() as $pagoCuota) {
                $cuotas[] = $pagoCuota->getCuota();
                $this->em->remove($pagoCuota);
            }

            // 3. Eliminar el pago
            $this->em->remove($pago);
            $this->em->flush();

            // Actualizar el estado de las cuotas antes asociadas al pago
            foreach ($cuotas as $cuota) {
                $cuota->setEstado($this->cuotaService->calcularEstado($cuota));
                $this->em->persist($cuota);
            }
            $this->em->flush();

            return ["estado" => "exito"];
            
        } catch (\Exception $e) {
            return ["estado" => "error", "exception" => $e->getMessage()];
        }
    }
}