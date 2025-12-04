<?php

namespace App\Tests\Service;

use App\Entity\Carrera;
use App\Entity\Cuota;
use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\DocumentacionNota;
use App\Entity\Edicion;
use App\Entity\InscripcionCarrera;
use App\Entity\InscripcionEdicion;
use App\Entity\Nota;
use App\Entity\Pago;
use App\Entity\PagoCuota;
use App\Service\AlumnoService;
use App\Service\CalculadorCuota;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class AlumnoServiceTest extends TestCase
{
    private AlumnoService $alumnoService;

    protected function setUp(): void
    {
        $this->alumnoService = new AlumnoService();
    }

    public function testPrepararCuotasData(): void
    {
        $calculadorMock = $this->createMock(CalculadorCuota::class);
        $calculadorMock->method('calcularEstado')->willReturn('Al día');

        // Case 1: Cuota de Carrera
        $carrera = $this->createMock(Carrera::class);
        $carrera->method('getNombre')->willReturn('Ingeniería');
        
        $inscripcionCarrera = $this->createMock(InscripcionCarrera::class);
        $inscripcionCarrera->method('getCarrera')->willReturn($carrera);

        $cuotaCarrera = $this->createMock(Cuota::class);
        $cuotaCarrera->method('getInscripcionCarrera')->willReturn($inscripcionCarrera);
        $cuotaCarrera->method('getInscripcionEdicion')->willReturn(null);

        // Case 2: Cuota de Edición
        $edicion = $this->createMock(Edicion::class);
        $edicion->method('getNombre')->willReturn('Edición 2023');
        
        $inscripcionEdicion = $this->createMock(InscripcionEdicion::class);
        $inscripcionEdicion->method('getEdicion')->willReturn($edicion);

        $cuotaEdicion = $this->createMock(Cuota::class);
        $cuotaEdicion->method('getInscripcionCarrera')->willReturn(null);
        $cuotaEdicion->method('getInscripcionEdicion')->willReturn($inscripcionEdicion);

        $cuotas = [$cuotaCarrera, $cuotaEdicion];

        $result = $this->alumnoService->prepararCuotasData($cuotas, $calculadorMock);

        $this->assertCount(2, $result);
        $this->assertEquals('Ingeniería', $result[0]['carreraCurso']);
        $this->assertEquals('Al día', $result[0]['estado']);
        $this->assertEquals('Edición 2023', $result[1]['carreraCurso']);
    }

    public function testPrepararCarrerasData(): void
    {
        $carrera = $this->createMock(Carrera::class);
        $carrera->method('getNombre')->willReturn('Carrera Test');
        $carrera->method('getNroOrdenanza')->willReturn(12345);
        $carrera->method('getNroImplementacion')->willReturn(123);
        $carrera->method('getId')->willReturn(1);

        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $inscripcion->method('getCarrera')->willReturn($carrera);

        $result = $this->alumnoService->prepararCarrerasData([$inscripcion]);

        $this->assertCount(1, $result);
        $this->assertEquals('Carrera Test', $result[0]['nombre']);
        $this->assertEquals(12345, $result[0]['nro_ordenanza']);
        $this->assertEquals(123, $result[0]['nro_implementacion']);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testPrepararCursosData(): void
    {
        // Setup Dates
        $now = new \DateTime();
        $past = (clone $now)->modify('-2 months');
        $future = (clone $now)->modify('+2 months');

        // Mock Edicion (En curso)
        $curso = $this->createMock(Curso::class);
        $curso->method('getNombre')->willReturn('Curso PHP');
        $curso->method('getHoras')->willReturn(20);
        $curso->method('getId')->willReturn(10);

        $edicion = $this->createMock(Edicion::class);
        $edicion->method('getCurso')->willReturn($curso);
        $edicion->method('getNombre')->willReturn('Edicion 1');
        $edicion->method('getFechaInicio')->willReturn($past);
        $edicion->method('getFechaFin')->willReturn($future);
        $edicion->method('getId')->willReturn(5);

        $inscripcion = $this->createMock(InscripcionEdicion::class);
        $inscripcion->method('getEdicion')->willReturn($edicion);

        $result = $this->alumnoService->prepararCursosData([$inscripcion]);

        $this->assertCount(1, $result);
        $this->assertEquals('En curso', $result[0]['estado']);
        $this->assertEquals('Curso PHP', $result[0]['curso']);
        $this->assertEquals('Edicion 1', $result[0]['edicion']);
        $this->assertEquals(20, $result[0]['horas']);
        $this->assertEquals(5, $result[0]['id_edicion']);
        $this->assertEquals(10, $result[0]['id_curso']);
    }

    public function testPrepararCarrerasInscripcion(): void
    {
        // 1. Setup Carrera Inscripta
        $carreraInscripta = $this->createMock(Carrera::class);
        $carreraInscripta->method('getId')->willReturn(1);
        $carreraInscripta->method('getNombre')->willReturn('Carrera 1');
        $carreraInscripta->method('getNroOrdenanza')->willReturn(12345);
        $carreraInscripta->method('getNroImplementacion')->willReturn(123);

        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $inscripcion->method('getCarrera')->willReturn($carreraInscripta);
        $inscripcion->method('getNroLegajo')->willReturn(123);
        $inscripcion->method('getDescuento')->willReturn(null);
        $inscripcion->method('getFechaInscripcion')->willReturn(new \DateTime('2023-01-15'));
        
        // 2. Setup Carrera No Inscripta
        $carreraNoInscripta = $this->createMock(Carrera::class);
        $carreraNoInscripta->method('getId')->willReturn(2);
        $carreraNoInscripta->method('getNombre')->willReturn('Carrera 2');
        $carreraNoInscripta->method('getNroOrdenanza')->willReturn(12345);
        $carreraNoInscripta->method('getNroImplementacion')->willReturn(123);

        // 3. Setup Cuota (simple mock, assuming no payments for simplicity)
        $cuota = $this->createMock(Cuota::class);
        $cuota->method('getInscripcionCarrera')->willReturn($inscripcion);
        $cuota->method('getId')->willReturn(100);
        $cuota->method('getNumeroCuota')->willReturn(1);

        $result = $this->alumnoService->prepararCarrerasInscripcion(
            [$inscripcion], 
            [$cuota], 
            [$carreraInscripta, $carreraNoInscripta],
            []
        );

        // Assertions
        $this->assertCount(2, $result);
        
        // Check sorting: Inscripto should be first
        $this->assertEquals('Inscripto', $result[0]['estado']);
        $this->assertEquals('No inscripto', $result[1]['estado']);
        
        // Check details for inscripta
        $this->assertEquals(123, $result[0]['legajo']);
        $this->assertEquals('Borrar', $result[0]['accion']);
        $this->assertEquals('2023-01-15', $result[0]['fechaInscripcion']);
        
        // Check details for no inscripta
        $this->assertNull($result[1]['legajo']);
        $this->assertEquals('Inscribir', $result[1]['accion']);
    }

    public function testCrearCuotasParaCarrera(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $carrera = $this->createMock(Carrera::class);
        $carrera->method('getCantidadCuotas')->willReturn(3);

        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $inscripcion->method('getCarrera')->willReturn($carrera);

        // Expect persist to be called 3 times (once for each cuota)
        $entityManager->expects($this->exactly(3))
            ->method('persist')
            ->with($this->isInstanceOf(Cuota::class));

        $entityManager->expects($this->once())
            ->method('flush');

        $this->alumnoService->crearCuotasParaCarrera($inscripcion, $entityManager);
    }

    public function testCrearCuotasParaCarreraSinCuotas(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $carrera = $this->createMock(Carrera::class);
        $carrera->method('getCantidadCuotas')->willReturn(0);

        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $inscripcion->method('getCarrera')->willReturn($carrera);

        // Expect persist to NOT be called
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $this->alumnoService->crearCuotasParaCarrera($inscripcion, $entityManager);
    }

    public function testPrepararEdicionesInscripcion(): void
    {
        // Setup Curso
        $curso = $this->createMock(Curso::class);
        $curso->method('getNombre')->willReturn('Curso Test');
        $curso->method('getNroOrdenanza')->willReturn(12345);
        $curso->method('getNroImplementacion')->willReturn(123);

        // Setup Edicion Inscripta
        $edicionInscripta = $this->createMock(Edicion::class);
        $edicionInscripta->method('getId')->willReturn(1);
        $edicionInscripta->method('getNombre')->willReturn('Edicion 1');
        $edicionInscripta->method('getCurso')->willReturn($curso);

        $inscripcion = $this->createMock(InscripcionEdicion::class);
        $inscripcion->method('getEdicion')->willReturn($edicionInscripta);
        $inscripcion->method('getNroLegajo')->willReturn(456);
        $inscripcion->method('getDescuento')->willReturn(null);
        $inscripcion->method('getFechaInscripcion')->willReturn(new \DateTime('2023-02-01'));

        // Setup Edicion No Inscripta
        $edicionNoInscripta = $this->createMock(Edicion::class);
        $edicionNoInscripta->method('getId')->willReturn(2);
        $edicionNoInscripta->method('getNombre')->willReturn('Edicion 2');
        $edicionNoInscripta->method('getCurso')->willReturn($curso);

        // Setup Cuota
        $cuota = $this->createMock(Cuota::class);
        $cuota->method('getInscripcionEdicion')->willReturn($inscripcion);
        $cuota->method('getId')->willReturn(200);
        $cuota->method('getNumeroCuota')->willReturn(1);

        $result = $this->alumnoService->prepararEdicionesInscripcion(
            [$inscripcion],
            [$cuota],
            [$edicionInscripta, $edicionNoInscripta],
            []
        );

        $this->assertCount(2, $result);
        $this->assertEquals('Inscripto', $result[0]['estado']);
        $this->assertEquals('No inscripto', $result[1]['estado']);
        $this->assertEquals(456, $result[0]['legajo']);
        $this->assertEquals('Borrar', $result[0]['accion']);
        $this->assertEquals('Inscribir', $result[1]['accion']);
    }

    public function testCrearCuotasParaEdicion(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $inscripcion = $this->createMock(InscripcionEdicion::class);

        // Expect persist to be called 1 time
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Cuota::class));

        $entityManager->expects($this->once())
            ->method('flush');

        $this->alumnoService->crearCuotasParaEdicion($inscripcion, $entityManager);
    }

    public function testEliminarCuotasDeInscripcionCarrera(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cuotaRepo = $this->createMock(EntityRepository::class);
        $pagoCuotaRepo = $this->createMock(EntityRepository::class);

        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $cuota = $this->createMock(Cuota::class);
        $pagoCuota = $this->createMock(PagoCuota::class);
        $pago = $this->createMock(Pago::class);

        $pagoCuota->method('getPago')->willReturn($pago);

        // Configure Repositories
        $entityManager->method('getRepository')
            ->willReturnCallback(function($class) use ($cuotaRepo, $pagoCuotaRepo) {
                if ($class === Cuota::class) {
                    return $cuotaRepo;
                }
                if ($class === \App\Entity\PagoCuota::class) {
                    return $pagoCuotaRepo;
                }
                return null;
            });

        // 1. Find Cuotas
        $cuotaRepo->expects($this->once())
            ->method('findBy')
            ->with(['inscripcionCarrera' => $inscripcion])
            ->willReturn([$cuota]);

        // 2. Find Pagos for that Cuota
        $pagoCuotaRepo->expects($this->once())
            ->method('findBy')
            ->with(['cuota' => $cuota])
            ->willReturn([$pagoCuota]);

        // 3. Expect Removals (3 removes: pagoCuota, pago, cuota)
        $entityManager->expects($this->exactly(3))->method('remove');

        $this->alumnoService->eliminarCuotasDeInscripcion($inscripcion, $entityManager, 'carrera');
    }

    public function testEliminarCuotasDeInscripcionEdicion(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cuotaRepo = $this->createMock(EntityRepository::class);
        $pagoCuotaRepo = $this->createMock(EntityRepository::class);

        $inscripcion = $this->createMock(InscripcionEdicion::class);
        $cuota = $this->createMock(Cuota::class);

        // Configure Repositories
        $entityManager->method('getRepository')
            ->willReturnCallback(function($class) use ($cuotaRepo, $pagoCuotaRepo) {
                if ($class === Cuota::class) {
                    return $cuotaRepo;
                }
                if ($class === \App\Entity\PagoCuota::class) {
                    return $pagoCuotaRepo;
                }
                return null;
            });

        // 1. Find Cuotas
        $cuotaRepo->expects($this->once())
            ->method('findBy')
            ->with(['inscripcionEdicion' => $inscripcion])
            ->willReturn([$cuota]);

        // 2. Find Pagos for that Cuota (none)
        $pagoCuotaRepo->expects($this->once())
            ->method('findBy')
            ->with(['cuota' => $cuota])
            ->willReturn([]);

        // 3. Expect only 1 removal (the cuota, no payments)
        $entityManager->expects($this->once())->method('remove')->with($cuota);

        $this->alumnoService->eliminarCuotasDeInscripcion($inscripcion, $entityManager, 'edicion');
    }

    public function testEliminarCuotasDeInscripcionTipoInvalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Tipo de inscripción inválido: invalido. Debe ser 'carrera' o 'edicion'.");

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $inscripcion = $this->createMock(InscripcionCarrera::class);

        $this->alumnoService->eliminarCuotasDeInscripcion($inscripcion, $entityManager, 'invalido');
    }

    public function testPrepararNotasDataConDocumentacion(): void
    {
        $curso = $this->createMock(Curso::class);
        $curso->method('getNombre')->willReturn('Curso A');

        $edicion = $this->createMock(Edicion::class);
        $edicion->method('getNombre')->willReturn('Edicion A');
        $edicion->method('getCurso')->willReturn($curso);

        $inscripcion = $this->createMock(InscripcionEdicion::class);
        $inscripcion->method('getEdicion')->willReturn($edicion);

        // Nota with documentation
        $doc = $this->createMock(DocumentacionNota::class);
        $doc->method('getArchivo')->willReturn('test.pdf');

        $nota = $this->createMock(Nota::class);
        $nota->method('getId')->willReturn(1);
        $nota->method('getInscripcionEdicion')->willReturn($inscripcion);
        $nota->method('getValor')->willReturn(9.0);
        $nota->method('getDescripcion')->willReturn('Excelente');
        $nota->method('getDocumentacionNota')->willReturn($doc);
        $nota->method('getFechaCarga')->willReturn(new \DateTime('2023-01-01'));

        $result = $this->alumnoService->prepararNotasData([$nota]);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Curso A', $result[0]['curso']);
        $this->assertEquals('Edicion A', $result[0]['edicion']);
        $this->assertEquals(9.0, $result[0]['nota']);
        $this->assertEquals('Excelente', $result[0]['descripcion']);
        $this->assertStringContainsString('test.pdf', $result[0]['documentacion']);
        $this->assertStringContainsString('Ver archivo', $result[0]['documentacion']);
        $this->assertEquals('01/01/2023', $result[0]['fecha_carga']);
    }

    public function testPrepararNotasDataSinDocumentacion(): void
    {
        $curso = $this->createMock(Curso::class);
        $curso->method('getNombre')->willReturn('Curso B');

        $edicion = $this->createMock(Edicion::class);
        $edicion->method('getNombre')->willReturn('Edicion B');
        $edicion->method('getCurso')->willReturn($curso);

        $inscripcion = $this->createMock(InscripcionEdicion::class);
        $inscripcion->method('getEdicion')->willReturn($edicion);

        $nota = $this->createMock(Nota::class);
        $nota->method('getId')->willReturn(2);
        $nota->method('getInscripcionEdicion')->willReturn($inscripcion);
        $nota->method('getValor')->willReturn(7.0);
        $nota->method('getDescripcion')->willReturn('Bueno');
        $nota->method('getDocumentacionNota')->willReturn(null);
        $nota->method('getFechaCarga')->willReturn(null);

        $result = $this->alumnoService->prepararNotasData([$nota]);

        $this->assertCount(1, $result);
        $this->assertEquals(7.0, $result[0]['nota']);
        $this->assertEquals('Sin documentación', $result[0]['documentacion']);
        $this->assertEquals('N/A', $result[0]['fecha_carga']);
    }
}