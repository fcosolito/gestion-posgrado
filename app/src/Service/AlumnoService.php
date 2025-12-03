<?php 

namespace App\Service;

use App\Entity\Alumno;
use App\Entity\InscripcionEdicion;
use App\Entity\InscripcionCarrera;
use App\Entity\Carrera;
use App\Entity\Edicion;
use App\Entity\Cuota;
use App\Entity\DocumentacionNota;
use App\Entity\Descuento;
use App\Entity\PerteneceA;
use App\Form\AlumnoType;
use App\Entity\Nota;
use App\Form\NotaType;
use App\Repository\AlumnoRepository;
use App\Repository\CarreraRepository;
use App\Repository\CursoRepository;
use App\Repository\EdicionRepository;
use App\Repository\InscripcionCarreraRepository;
use App\Repository\InscripcionEdicionRepository;
use App\Service\CalculadorCuota;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlumnoService
{
    public function prepararCuotasData(array $cuotas, CalculadorCuota $calculadorCuota): array{
        $cuotasData = [];
        foreach ($cuotas as $cuota) {
            // Factorice lo anterior en este metodo, comprobar si funciona igual
            $estadoPago = $calculadorCuota->calcularEstado($cuota);


            if ($cuota->getInscripcionCarrera() === null) {
                $carreraCurso = $cuota->getInscripcionEdicion()->getEdicion()->getNombre();
            } else {
                $carreraCurso = $cuota->getInscripcionCarrera()->getCarrera()->getNombre();
            }


            $cuotasData[] = [
                'carreraCurso' => $carreraCurso,
                'estado' => $estadoPago
            ];
        }
        return $cuotasData;
    }

    public function prepararCarrerasData(array $inscripcionesCarrera): array{
        $carrerasData = [];
        foreach ($inscripcionesCarrera as $inscripcion) {
            $carrera = $inscripcion->getCarrera();
            $carrerasData[] = [
                'nombre' => $carrera->getNombre(),
                'nro_ordenanza' => $carrera->getNroOrdenanza(),
                'nro_implementacion' => $carrera->getNroImplementacion(),
                'id' => $carrera->getId()
            ];
        }
        return $carrerasData;
    }

    public function prepararCursosData(array $inscripcionesEdicion): array{
        $cursosData = [];
        $fechaActual = new \DateTime();
        
        foreach ($inscripcionesEdicion as $inscripcion) {
            $edicion = $inscripcion->getEdicion();
            $curso = $edicion->getCurso();
            
            // Determinar el estado basado en las fechas
            $fechaInicio = $edicion->getFechaInicio();
            $fechaFin = $edicion->getFechaFin();
            
            if ($fechaActual < $fechaInicio) {
                $estado = 'Próximo';
            } elseif ($fechaFin && $fechaActual > $fechaFin) {
                $estado = 'Finalizado';
            } else {
                $estado = 'En curso';
            }
            
            $cursosData[] = [
                'curso' => $curso->getNombre(),
                'edicion' => $edicion->getNombre(),
                'estado' => $estado,
                'horas' => $curso->getHoras(),
                'id_edicion' => $edicion->getId(),
                'id_curso' => $curso->getId()
            ];
        }
        return $cursosData;
    }

    public function prepararCarrerasInscripcion(array $inscripcionesCarrera, array $cuotas, array $carreras): array{
        // Crear mapas de inscripciones y cuotas por carrera
        $inscripcionesPorCarrera = [];
        $carrerasInscriptas = [];

        foreach ($inscripcionesCarrera as $inscripcion) {
            $carreraId = $inscripcion->getCarrera()->getId();
            $carrerasInscriptas[] = $carreraId;
            $inscripcionesPorCarrera[$carreraId] = $inscripcion;
        }

        // Agrupar cuotas por carrera y calcular estados
        $cuotasPorCarrera = [];
        
        foreach ($cuotas as $cuota) {
            $inscripcion = $cuota->getInscripcionCarrera();
            
            if ($inscripcion) {
                $carreraId = $inscripcion->getCarrera()->getId();
                $cuotaId = $cuota->getId();
                
                if (!isset($cuotasPorCarrera[$carreraId])) {
                    $cuotasPorCarrera[$carreraId] = [];
                }
                
                // Calcular montos de la cuota
                $montoPagado = 0;
                $montoCuota = 0;
                $contadorPagos = 0;
                
                if (isset($pagosPorCuota[$cuotaId])) {
                    foreach ($pagosPorCuota[$cuotaId] as $pagoCuota) {
                        $montoCuota = $pagoCuota->getMontoCuota();
                        $montoPagado += $pagoCuota->getPago()->getMonto();
                        $contadorPagos++;
                    }
                }
                
                // Determinar estado
                if ($contadorPagos === 0) {
                    $estado = 'Pendiente';
                } elseif ($montoPagado >= $montoCuota) {
                    $estado = 'Paga';
                } else {
                    $estado = 'Parcial';
                }
                
                $cuotasPorCarrera[$carreraId][] = [
                    'numero' => $cuota->getNumeroCuota(),
                    'monto' => $montoCuota,
                    'pagado' => $montoPagado,
                    'estado' => $estado
                ];
            }
        }

        // Preparar datos para todas las carreras
        $carrerasData = [];
        foreach ($carreras as $carrera) {
            $carreraId = $carrera->getId();

            // Verificar si el alumno está inscripto en esta carrera
            if (in_array($carreraId, $carrerasInscriptas)) {
                $estado = 'Inscripto';
                $accion = 'Borrar';
                
                // Obtener datos de la inscripción
                $inscripcion = $inscripcionesPorCarrera[$carreraId];
                $descuento = $inscripcion->getDescuento();
                
                // Extraer valores del descuento si existe
                $valorDescuento = $descuento ? $descuento->getValor() : 0;
                $descripcionDescuento = $descuento ? $descuento->getDescripcion() : '';
                
                // Extraer valores de la inscripción
                $legajo = $inscripcion->getNroLegajo() ?? 'Sin legajo';
                $fechaInscripcion = $inscripcion->getFechaInscripcion() ? $inscripcion->getFechaInscripcion()->format('Y-m-d') : null;
                
                // Obtener cuotas de esta carrera
                $cuotasInfo = $cuotasPorCarrera[$carreraId] ?? [];
            } else {
                $estado = 'No inscripto';
                $accion = 'Inscribir';
                $valorDescuento = 0;
                $descripcionDescuento = '';
                $legajo = null;
                $fechaInscripcion = null;
                $cuotasInfo = [];
            }

            $carrerasData[] = [
                'id' => $carreraId,
                'nombre' => $carrera->getNombre(),
                'ordenanza' => $carrera->getNroOrdenanza(),
                'implementacion' => $carrera->getNroImplementacion(),
                'legajo' => $legajo,
                'descuento' => $valorDescuento,
                'descripcion' => $descripcionDescuento,
                'fechaInscripcion' => $fechaInscripcion,
                'estado' => $estado,
                'accion' => $accion,
                'cuotas' => $cuotasInfo
            ];
        }

        // Ordenar carreras por estado (inscriptas primero)
        usort($carrerasData, function($a, $b) {
            if ($a['estado'] === 'Inscripto' && $b['estado'] !== 'Inscripto') {
                return -1;
            }
            if ($a['estado'] !== 'Inscripto' && $b['estado'] === 'Inscripto') {
                return 1;
            }
            return 0;
        });

        return $carrerasData;

    }

    public function crearCuotasParaCarrera(InscripcionCarrera $inscripcion, EntityManagerInterface $entityManager): void{
        $carrera = $inscripcion->getCarrera();
        $cantidadCuotas = $carrera->getCantidadCuotas();

        // Si la carrera no tiene cantidad de cuotas definida, no crear cuotas
        if (!$cantidadCuotas || $cantidadCuotas <= 0) {
            return;
        }

        // Crear cada cuota
        for ($i = 1; $i <= $cantidadCuotas; $i++) {
            $cuota = new Cuota();
            $cuota->setInscripcionCarrera($inscripcion);
            $cuota->setNumeroCuota($i);
            $entityManager->persist($cuota);
        }

        $entityManager->flush();
    }

    public function prepararEdicionesInscripcion(array $inscripcionesEdicion, array $cuotas, array $ediciones): array{
        // Crear mapa de inscripciones y cuotas por edicion
        $inscripcionesPorEdicion = [];
        $edicionesInscriptas = [];

        foreach ($inscripcionesEdicion as $inscripcion) {
            $edicionId = $inscripcion->getEdicion()->getId();
            $edicionesInscriptas[] = $edicionId;
            $inscripcionesPorEdicion[$edicionId] = $inscripcion;
        }

        // Agrupar cuotas por edicion y calcular estados
        $cuotasPorEdicion = [];
        
        foreach ($cuotas as $cuota) {
            $inscripcion = $cuota->getInscripcionEdicion();
            
            if ($inscripcion) {
                $edicionId = $inscripcion->getEdicion()->getId();
                $cuotaId = $cuota->getId();
                
                if (!isset($cuotasPorEdicion[$edicionId])) {
                    $cuotasPorEdicion[$edicionId] = [];
                }
                
                // Calcular montos de la cuota (misma lógica que visualizar)
                $montoPagado = 0;
                $montoCuota = 0;
                $contadorPagos = 0;
                
                if (isset($pagosPorCuota[$cuotaId])) {
                    foreach ($pagosPorCuota[$cuotaId] as $pagoCuota) {
                        $montoCuota = $pagoCuota->getMontoCuota();
                        $montoPagado += $pagoCuota->getPago()->getMonto();
                        $contadorPagos++;
                    }
                }
                
                // Determinar estado
                if ($contadorPagos === 0) {
                    $estado = 'Pendiente';
                } elseif ($montoPagado >= $montoCuota) {
                    $estado = 'Paga';
                } else {
                    $estado = 'Parcial';
                }
                
                $cuotasPorEdicion[$edicionId][] = [
                    'numero' => $cuota->getNumeroCuota(),
                    'monto' => $montoCuota,
                    'pagado' => $montoPagado,
                    'estado' => $estado
                ];
            }
        }

        // Preparar datos para todas las ediciones
        $edicionesData = [];
        foreach ($ediciones as $edicion) {
            $edicionId = $edicion->getId();

            // Verificar si el alumno está inscripto en esta edicion
            if (in_array($edicionId, $edicionesInscriptas)) {
                $estado = 'Inscripto';
                $accion = 'Borrar';
                
                // Obtener datos de la inscripción
                $inscripcion = $inscripcionesPorEdicion[$edicionId];
                $descuento = $inscripcion->getDescuento();
                
                // Extraer valores del descuento si existe
                $valorDescuento = $descuento ? $descuento->getValor() : 0;
                $descripcionDescuento = $descuento ? $descuento->getDescripcion() : '';
                
                // Extraer valores de la inscripción
                $legajo = $inscripcion->getNroLegajo() ?? 'Sin legajo';
                $fechaInscripcion = $inscripcion->getFechaInscripcion() ? $inscripcion->getFechaInscripcion()->format('Y-m-d') : null;
                
                // Obtener cuotas de esta edicion
                $cuotasInfo = $cuotasPorEdicion[$edicionId] ?? [];
            } else {
                $estado = 'No inscripto';
                $accion = 'Inscribir';
                $valorDescuento = 0;
                $descripcionDescuento = '';
                $legajo = null;
                $fechaInscripcion = null;
                $cuotasInfo = [];
            }

            $edicionesData[] = [
                'id' => $edicionId,
                'nombre' => $edicion->getNombre(),
                'curso' => $edicion->getCurso()->getNombre(),
                'ordenanza' => $edicion->getCurso()->getNroOrdenanza(),
                'implementacion' => $edicion->getCurso()->getNroImplementacion(),
                'legajo' => $legajo,
                'descuento' => $valorDescuento,
                'descripcion' => $descripcionDescuento,
                'fechaInscripcion' => $fechaInscripcion,
                'estado' => $estado,
                'accion' => $accion,
                'cuotas' => $cuotasInfo
            ];
        }

        // Ordenar ediciones por estado (inscriptas primero)
        usort($edicionesData, function($a, $b) {
            if ($a['estado'] === 'Inscripto' && $b['estado'] !== 'Inscripto') {
                return -1;
            }
            if ($a['estado'] !== 'Inscripto' && $b['estado'] === 'Inscripto') {
                return 1;
            }
            return 0;
        });
        
        return $edicionesData;
    }

    public function crearCuotasParaEdicion(InscripcionEdicion $inscripcion, EntityManagerInterface $entityManager): void{
        // esta funcion crea una sola cuota, en este momento los cursos o ediciones no tienen un atributo de numero de cuotas
        $cuota = new Cuota();
        $cuota->setInscripcionEdicion($inscripcion);
        $cuota->setNumeroCuota(1);
       
        $entityManager->persist($cuota);
        $entityManager->flush();
    }

    public function eliminarCuotasDeInscripcion($inscripcion, EntityManagerInterface $entityManager, string $tipo = 'carrera'): void{
        // Se buscan todas las cuotas asociadas a esta inscripción según el tipo
        if ($tipo === 'carrera') {
            $cuotas = $entityManager->getRepository(Cuota::class)
                ->findBy(['inscripcionCarrera' => $inscripcion]);
        } else if ($tipo === 'edicion') {
            $cuotas = $entityManager->getRepository(Cuota::class)
                ->findBy(['inscripcionEdicion' => $inscripcion]);
        } else{
            throw new \InvalidArgumentException("Tipo de inscripción inválido: $tipo. Debe ser 'carrera' o 'edicion'.");
        }

        // Eliminar cada cuota y sus pagos asociados
        foreach ($cuotas as $cuota) {
            // Primero obtener los pagos de cuota asociados
            $pagosCuotas = $entityManager->getRepository(\App\Entity\PagoCuota::class)
                ->findBy(['cuota' => $cuota]);
            
            foreach ($pagosCuotas as $pagoCuota) {
                // Obtener el pago antes de eliminar la relación
                $pago = $pagoCuota->getPago();
                
                // Eliminar la relación PagoCuota
                $entityManager->remove($pagoCuota);
                
                // Eliminar el Pago
                if ($pago) {
                    $entityManager->remove($pago);
                }
            }
            
            // Luego eliminar la cuota
            $entityManager->remove($cuota);
        }
        
        // No hacemos flush aquí, se hará junto con la eliminación de la inscripción
    }

    public function prepararNotasData(array $notas): array{
        $notasData = [];

        foreach ($notas as $nota) {
            $inscripcion = $nota->getInscripcionEdicion();
            $edicion = $inscripcion->getEdicion();
            $curso = $edicion->getCurso();
            
            // Generar enlace para el archivo si existe
            $documentacionHtml = 'Sin documentación';
            if ($nota->getDocumentacionNota()) {
                $archivo = $nota->getDocumentacionNota()->getArchivo();
                $documentacionHtml = sprintf(
                    '<a href="/uploads/documentos_notas/%s" target="_blank" class="btn-documento">Ver archivo</a>',
                    $archivo
                );
            }
            
            $notasData[] = [
                'id' => $nota->getId(),
                'curso' => $curso->getNombre(),
                'edicion' => $edicion->getNombre(),
                'nota' => $nota->getValor(),
                'descripcion' => $nota->getDescripcion(),
                'documentacion' => $documentacionHtml,
                'fecha_carga' => $nota->getFechaCarga() ? $nota->getFechaCarga()->format('d/m/Y') : 'N/A',
            ];
        }
        return $notasData;
    }
}