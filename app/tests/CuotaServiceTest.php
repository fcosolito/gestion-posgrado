<?php

namespace App\Tests\Service;

use App\Service\CuotaService;
use App\Service\CalculadorCuota;

use App\Entity\Carrera;
use App\Entity\Curso;
use App\Entity\Edicion;
use App\Entity\Alumno;
use App\Entity\Cuota;
use App\Entity\PagoCuota;
use App\Entity\Pago;
use App\Entity\Comprobante;
use App\Entity\InscripcionCarrera;
use App\Repository\CarreraRepository;
use App\Repository\CursoRepository;
use App\Repository\EdicionRepository;
use App\Repository\AlumnoRepository;
use App\Repository\CuotaRepository;
use App\Repository\PagoCuotaRepository;
use App\Repository\PrecioCarreraRepository;
use App\Repository\InscripcionEdicionRepository;

use Doctrine\ORM\EntityManagerInterface;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CuotaServiceTest extends TestCase
{
    private CarreraRepository&MockObject $carreraR;
    private CursoRepository&MockObject $cursoR;
    private EdicionRepository&MockObject $edicionR;
    private AlumnoRepository&MockObject $alumnoR;
    private CuotaRepository&MockObject $cuotaR;
    private PagoCuotaRepository&MockObject $pagoCuotaR;
    private PrecioCarreraRepository&MockObject $precioCarreraR;
    private CalculadorCuota&MockObject $calculadorCuota;
    private InscripcionEdicionRepository&MockObject $inscEdicionR;
    private EntityManagerInterface&MockObject $em;

    private CuotaService $service;

    protected function setUp(): void
    {
        $this->carreraR = $this->createMock(CarreraRepository::class);
        $this->cursoR = $this->createMock(CursoRepository::class);
        $this->edicionR = $this->createMock(EdicionRepository::class);
        $this->alumnoR = $this->createMock(AlumnoRepository::class);
        $this->cuotaR = $this->createMock(CuotaRepository::class);
        $this->pagoCuotaR = $this->createMock(PagoCuotaRepository::class);
        $this->precioCarreraR = $this->createMock(PrecioCarreraRepository::class);
        $this->calculadorCuota = $this->createMock(CalculadorCuota::class);
        $this->inscEdicionR = $this->createMock(InscripcionEdicionRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->service = new CuotaService(
            $this->carreraR,
            $this->cursoR,
            $this->edicionR,
            $this->alumnoR,
            $this->cuotaR,
            $this->pagoCuotaR,
            $this->precioCarreraR,
            $this->calculadorCuota,
            $this->inscEdicionR,
            $this->em
        );
    }

    // ============================================================
    // obtenerDatosIndex
    // ============================================================
    public function testObtenerDatosIndexSinFiltrosDevuelveTodas(): void
    {
        $cuota = $this->crearCuota(1);

        $this->cuotaR
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$cuota]);

        $this->pagoCuotaR
            ->method('findBy')
            ->willReturn([]);

        $this->calculadorCuota->method('calcularValor')->willReturn(1000.0);
        $this->calculadorCuota->method('calcularEstado')->willReturn("pendiente");

        $res = $this->service->obtenerDatosIndex([]);

        $this->assertArrayHasKey("cuotas", $res);
        $this->assertCount(1, $res["cuotas"]);
        $this->assertEquals(1, $res["cuotas"][0]["id"]);
    }

    public function testObtenerDatosIndexConFiltroCarrera(): void
    {
        $carrera = $this->crearCarrera(5, "Ing Sistemas");
        $cuota = $this->crearCuota(1);

        $this->carreraR
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($carrera);

        $this->cuotaR
            ->expects($this->once())
            ->method('findByCarrera')
            ->with($carrera)
            ->willReturn([$cuota]);

        $this->pagoCuotaR->method('findBy')->willReturn([]);
        $this->calculadorCuota->method('calcularValor')->willReturn(500.0);
        $this->calculadorCuota->method('calcularEstado')->willReturn("pendiente");

        $res = $this->service->obtenerDatosIndex(["carrera" => 5]);

        $this->assertEquals(1, $res["cuotas"][0]["id"]);
        $this->assertEquals("Ing Sistemas", $res["carrera"]["nombre"]);
    }

    public function testObtenerDatosIndexConFiltroAlumnoIntersecta(): void
    {
        $alumno = $this->crearAlumno(10, "Juan", "Perez");

        $c1 = $this->crearCuota(1);
        $c2 = $this->crearCuota(2);
        $soloAlumno = $this->crearCuota(2); // solo interseccion id=2

        $this->alumnoR->method('find')->with(10)->willReturn($alumno);

        // viene de filtros previos
        $this->cuotaR->method('findByCarrera')->willReturn([$c1, $c2]);

        // cuotas del alumno
        $this->cuotaR->method('findByAlumno')->willReturn([$soloAlumno]);

        $this->pagoCuotaR->method('findBy')->willReturn([]);
        $this->calculadorCuota->method('calcularValor')->willReturn(700.0);
        $this->calculadorCuota->method('calcularEstado')->willReturn("pendiente");

        $res = $this->service->obtenerDatosIndex([
            "carrera" => 1,
            "alumno" => 10
        ]);

        // solo cuota con id 2 debe quedar
        $this->assertCount(1, $res["cuotas"]);
        $this->assertEquals(2, $res["cuotas"][0]["id"]);
    }

    // ============================================================
    // serializarCuota (implícito dentro del index)
    // ============================================================
    public function testSerializarCuotaIncluyePagosYValores(): void
    {
        $cuota = $this->crearCuota(1);

        // Pago Cuota
        $pago = $this->createMock(PagoCuota::class);

        $pag = $this->createMock(Pago::class);
        $pag->method('getId')->willReturn(9);
        $pag->method('getMonto')->willReturn(5000.0);
        $pag->method('getFechaPago')->willReturn(new \DateTime("2024-01-01"));

        $comp = $this->createMock(Comprobante::class);
        $comp->method('getArchivo')->willReturn("archivo.pdf");
        $comp->method('getId')->willReturn(3);

        $pag->method('getComprobante')->willReturn($comp);

        $pago->method('getPago')->willReturn($pag);
        $pago->method('getMontoCuota')->willReturn(3000.0);

        $this->pagoCuotaR
            ->method('findBy')
            ->with(["cuota" => $cuota])
            ->willReturn([$pago]);

        $this->calculadorCuota->method('calcularValor')->willReturn(10000.0);
        $this->calculadorCuota->method('calcularEstado')->willReturn("parcial");

        $res = $this->service->obtenerDatosIndex([]);

        // El primer resultado será esta cuota, serializada
        $this->cuotaR->method('findAll')->willReturn([$cuota]);

        $res = $this->service->obtenerDatosIndex([]);

        $this->assertEquals(1, $res["cuotas"][0]["id"]);
        $this->assertEquals(3000, $res["cuotas"][0]["montoTotalAsociado"]);
        $this->assertCount(1, $res["cuotas"][0]["pagos"]);
        $this->assertEquals("parcial", $res["cuotas"][0]["estado"]);
        $this->assertEquals(10000, $res["cuotas"][0]["valor"]);
    }

    // ============================================================
    // create
    // ============================================================
    public function testCreatePersisteYFlushea(): void
    {
        $cuota = $this->crearCuota(20);

        $this->em->expects($this->once())->method('persist')->with($cuota);
        $this->em->expects($this->once())->method('flush');

        $this->service->create($cuota);
        $this->assertTrue(true);
    }

    public function testCreateLanzaExcepcionSiFalla(): void
    {
        $cuota = $this->crearCuota(20);

        $this->em->method('persist')->willThrowException(new \Exception("error"));

        $this->expectException(\RuntimeException::class);
        $this->service->create($cuota);
    }

    // ============================================================
    // update
    // ============================================================
    public function testUpdateFlushea(): void
    {
        $cuota = $this->crearCuota(1);

        $this->em->expects($this->once())->method('flush');

        $this->service->update($cuota);
        $this->assertTrue(true);
    }

    public function testUpdateLanzaExcepcionSiFalla(): void
    {
        $cuota = $this->crearCuota(1);

        $this->em->method('flush')->willThrowException(new \Exception("x"));

        $this->expectException(\RuntimeException::class);
        $this->service->update($cuota);
    }

    // ============================================================
    // delete
    // ============================================================
    public function testDeleteEliminaYFlushea(): void
    {
        $cuota = $this->crearCuota(33);

        $this->em->expects($this->once())->method('remove')->with($cuota);
        $this->em->expects($this->once())->method('flush');

        $this->service->delete($cuota);
        $this->assertTrue(true);
    }

    public function testDeleteLanzaExcepcionSiFalla(): void
    {
        $cuota = $this->crearCuota(33);

        $this->em->method('remove')->willThrowException(new \Exception("err"));

        $this->expectException(\RuntimeException::class);
        $this->service->delete($cuota);
    }

    // ============================================================
    // Helpers
    // ============================================================
    private function crearCuota(int $id): Cuota
    {
        $cuota = $this->createMock(Cuota::class);
        $cuota->method('getId')->willReturn($id);
        $cuota->method('getNumeroCuota')->willReturn(1);
        $cuota->method('getInscripcionCarrera')->willReturn(null);
        $cuota->method('getInscripcionEdicion')->willReturn(null);
        return $cuota;
    }

    private function crearCarrera(int $id, string $nombre): Carrera
    {
        $c = $this->createMock(Carrera::class);
        $c->method('getId')->willReturn($id);
        $c->method('getNombre')->willReturn($nombre);
        $c->method('getNroOrdenanza')->willReturn(12345);
        $c->method('getNroImplementacion')->willReturn(123);
        return $c;
    }

    private function crearAlumno(int $id, string $nombre, string $apellido): Alumno
    {
        $a = $this->createMock(Alumno::class);
        $a->method('getId')->willReturn($id);
        $a->method('getNombre')->willReturn($nombre);
        $a->method('getApellido')->willReturn($apellido);
        $a->method('getEmail')->willReturn("test@test.com");
        $a->method('getDni')->willReturn(4321);
        return $a;
    }
}
