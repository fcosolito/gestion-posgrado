<?php 

namespace App\Service;

use App\Entity\Cuota;
use App\Repository\PagoCuotaRepository;
use App\Repository\PrecioCarreraRepository;

class CalculadorCuota
{
    public function __construct(private PagoCuotaRepository $pagoCuotaR, private PrecioCarreraRepository $precioCarreraR) {}

    public function calcularEstado(Cuota $cuota): string
    {
        $pagoCuotas = $this->pagoCuotaR->findBy(["cuota" => $cuota]);
        $montoPagado = 0;
        $contadorPagos = 0;
        foreach ($pagoCuotas as $pagoCuota) {
            // Sumar el monto de cada PagoCuota (que es la parte del pago asignada a esta cuota)
            $montoPagado += $pagoCuota->getMontoCuota();
            $contadorPagos += 1;
        }
        
        // Determinar el estado de pago
        // Nota: Como no tenemos el monto exacto que se debe pagar por la cuota,
        // asumimos que si hay pagos registrados, la cuota está pagada
        if($contadorPagos === 0){
            $estadoPago = 'Pendiente';
        } elseif ($montoPagado > 0) {
            // Si hay pagos con monto mayor a 0, consideramos la cuota como pagada
            // En el futuro, esto debería compararse con el monto real de la cuota
            $estadoPago = 'Paga'; 
        } else {
            $estadoPago = 'Pendiente';
        }

        return $estadoPago;
    }
    public function calcularValor(Cuota $cuota): float
    {
        $inscCarrera = $cuota->getInscripcionCarrera();
        $inscEdicion = $cuota->getInscripcionEdicion();
        // calcular valor
        if ($inscCarrera) {
            $precioCarrera = $this->precioCarreraR->findPrecioVigentePorCarrera($inscCarrera->getCarrera()->getId());
            $valor = $precioCarrera ? $precioCarrera->getPrecio() : 0;
        } elseif ($inscEdicion) {
            $valor = $inscEdicion->getEdicion()->getPrecio() ?? 0;
        } else {
            $valor = 0;
        }

        return $valor;
    }
}