<?php

namespace App\Tests\Service;

use App\Entity\Carrera;
use App\Entity\Curso;
use App\Entity\PerteneceA;
use App\Entity\Descuento;
use App\Entity\InscripcionCarrera;
use App\Entity\Alumno;
use App\Entity\Cuota;
use App\Entity\PrecioCarrera;
use App\Repository\CarreraRepository;
use App\Repository\InscripcionCarreraRepository;
use App\Repository\PerteneceARepository;
use App\Repository\PrecioCarreraRepository;
use App\Repository\DescuentoRepository;
use App\Service\CarreraService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CarreraServiceTest extends TestCase
{
    private CarreraService $carreraService;
    private MockObject $carreraRepository;
    private MockObject $inscripcionCarreraRepository;
    private MockObject $perteneceARepository;
    private MockObject $precioCarreraRepository;
    private MockObject $descuentoRepository;
    private MockObject $entityManager;

    protected function setUp(): void
    {
        $this->carreraRepository = $this->createMock(CarreraRepository::class);
        $this->inscripcionCarreraRepository = $this->createMock(InscripcionCarreraRepository::class);
        $this->perteneceARepository = $this->createMock(PerteneceARepository::class);
        $this->precioCarreraRepository = $this->createMock(PrecioCarreraRepository::class);
        $this->descuentoRepository = $this->createMock(DescuentoRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->carreraService = new CarreraService(
            $this->carreraRepository,
            $this->inscripcionCarreraRepository,
            $this->perteneceARepository,
            $this->precioCarreraRepository,
            $this->descuentoRepository,
            $this->entityManager
        );
    }

    public function testGetIndexDataSuccess(): void
    {
        // Arrange
        $criteria = ['nombre' => 'Ingeniería'];
        $carrera = $this->createMock(Carrera::class);
        $carrera->method('getId')->willReturn(1);
        
        $this->carreraRepository->method('search')
            ->with($criteria)
            ->willReturn([$carrera]);
        
        $this->inscripcionCarreraRepository->method('findByCarrera')
            ->with(1)
            ->willReturn(['insc1', 'insc2']);

        // Act
        $result = $this->carreraService->getIndexData($criteria);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('carreras', $result);
        $this->assertArrayHasKey('inscriptosPorCarrera', $result);
        $this->assertEquals([$carrera], $result['carreras']);
        $this->assertEquals([1 => 2], $result['inscriptosPorCarrera']);
    }

    public function testGetIndexDataWhenSearchReturnsEmpty(): void
    {
        // Arrange
        $this->carreraRepository->method('search')
            ->willReturn([]);

        // Act
        $result = $this->carreraService->getIndexData([]);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('carreras', $result);
        $this->assertArrayHasKey('inscriptosPorCarrera', $result);
        $this->assertEquals([], $result['carreras']);
        $this->assertEquals([], $result['inscriptosPorCarrera']);
    }

    public function testGetIndexDataWhenRepositoryThrowsException(): void
    {
        // Arrange
        $this->carreraRepository->method('search')
            ->willThrowException(new \Exception('DB Error'));

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error al cargar el listado de carreras: DB Error');

        // Act
        $this->carreraService->getIndexData([]);
    }

    public function testBuildShowDataSuccess(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $carrera->method('getId')->willReturn(1);
        
        $curso1 = $this->createMock(Curso::class);
        $curso2 = $this->createMock(Curso::class);
        
        $relacion1 = $this->createMock(PerteneceA::class);
        $relacion1->method('isEsElectivo')->willReturn(false);
        $relacion1->method('getCurso')->willReturn($curso1);
        
        $relacion2 = $this->createMock(PerteneceA::class);
        $relacion2->method('isEsElectivo')->willReturn(true);
        $relacion2->method('getCurso')->willReturn($curso2);
        
        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $inscripcion->method('getId')->willReturn(10);
        $inscripcion->method('getNroLegajo')->willReturn(123);
        $inscripcion->method('getFechaInscripcion')->willReturn(new \DateTime('2024-01-01'));
        
        $alumno = $this->createMock(Alumno::class);
        $alumno->method('getId')->willReturn(100);
        $alumno->method('getNombre')->willReturn('Juan');
        $alumno->method('getApellido')->willReturn('Pérez');
        $alumno->method('getDni')->willReturn(12345678);
        $alumno->method('getEmail')->willReturn('juan@example.com');
        
        $inscripcion->method('getAlumno')->willReturn($alumno);
        $inscripcion->method('getDescuento')->willReturn(null);
        
        $descuento = $this->createMock(Descuento::class);
        $descuento->method('getId')->willReturn(1);
        $descuento->method('getValor')->willReturn(10.5);
        $descuento->method('getDescripcion')->willReturn('Descuento prueba');
        
        $precioCarrera = $this->createMock(PrecioCarrera::class);
        
        // Configurar mocks
        $this->perteneceARepository->method('findBy')
            ->with(['carrera' => $carrera])
            ->willReturn([$relacion1, $relacion2]);
        
        $this->inscripcionCarreraRepository->method('findByCarrera')
            ->with(1)
            ->willReturn([$inscripcion]);
        
        $this->descuentoRepository->method('findAll')
            ->willReturn([$descuento]);
        
        $this->precioCarreraRepository->method('findPrecioVigentePorCarrera')
            ->with(1)
            ->willReturn($precioCarrera);

        // Act
        $result = $this->carreraService->buildShowData($carrera);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($carrera, $result['data']['carrera']);
        $this->assertEquals(['id' => 1], $result['data']['carrera_ser']);
        $this->assertEquals(1, $result['data']['inscriptosCarrera']);
        $this->assertEquals([$curso1], $result['data']['cursosObligatorios']);
        $this->assertEquals([$curso2], $result['data']['cursosElectivos']);
        $this->assertCount(1, $result['data']['alumnos']);
        $this->assertEquals($precioCarrera, $result['data']['precioVigente']);
    }

    public function testBuildShowDataWhenExceptionOccurs(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        
        $this->perteneceARepository->method('findBy')
            ->willThrowException(new \Exception('DB Error'));

        // Act
        $result = $this->carreraService->buildShowData($carrera);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Ocurrió un error al cargar la información de la carrera', $result['error']);
    }

    public function testAsignarCursoExistenteSuccess(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $curso = $this->createMock(Curso::class);
        $cursoId = 1;
        $tipo = 'obligatorio';
        
        $cursoRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $cursoRepository->method('find')
            ->with($cursoId)
            ->willReturn($curso);
        
        $this->entityManager->method('getRepository')
            ->with(Curso::class)
            ->willReturn($cursoRepository);
        
        $this->perteneceARepository->method('findOneBy')
            ->with(['carrera' => $carrera, 'curso' => $curso])
            ->willReturn(null);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(PerteneceA::class));
        
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->carreraService->asignarCursoExistente($carrera, $cursoId, $tipo);
    }

    public function testAsignarCursoExistenteWhenCursoNotFound(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $cursoId = 999;
        
        $cursoRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $cursoRepository->method('find')->with($cursoId)->willReturn(null);
        
        $this->entityManager->method('getRepository')
            ->with(Curso::class)
            ->willReturn($cursoRepository);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El curso seleccionado no existe.');

        // Act
        $this->carreraService->asignarCursoExistente($carrera, $cursoId, 'obligatorio');
    }

    public function testAsignarCursoExistenteWhenAlreadyAssociated(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $curso = $this->createMock(Curso::class);
        $cursoId = 1;
        
        $cursoRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $cursoRepository->method('find')->with($cursoId)->willReturn($curso);
        
        $this->entityManager->method('getRepository')
            ->with(Curso::class)
            ->willReturn($cursoRepository);
        
        $existingRelation = $this->createMock(PerteneceA::class);
        $this->perteneceARepository->method('findOneBy')
            ->with(['carrera' => $carrera, 'curso' => $curso])
            ->willReturn($existingRelation);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El curso ya está asociado a esta carrera.');

        // Act
        $this->carreraService->asignarCursoExistente($carrera, $cursoId, 'obligatorio');
    }

    public function testCrearYAsignarCursoSuccess(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $data = [
            'nombre' => 'Nuevo Curso',
            'cantidadHoras' => '40',
            'tipo_asignacion' => 'obligatorio',
            'nroOrdenanza' => '123',
            'nroImplementacion' => '456'
        ];
        
        $persistCalls = [];
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($object) use (&$persistCalls) {
                $persistCalls[] = get_class($object);
            });
        
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->carreraService->crearYAsignarCurso($carrera, $data);

        // Assert
        $this->assertContains(Curso::class, $persistCalls);
        $this->assertContains(PerteneceA::class, $persistCalls);
    }

    public function testCrearYAsignarCursoWithInvalidNombre(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $data = [
            'nombre' => '',
            'cantidadHoras' => '40',
            'tipo_asignacion' => 'obligatorio'
        ];

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Debe ingresar un nombre para el curso.');

        // Act
        $this->carreraService->crearYAsignarCurso($carrera, $data);
    }

    public function testCrearYAsignarCursoWithInvalidHoras(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $data = [
            'nombre' => 'Curso Test',
            'cantidadHoras' => 'no-numerico',
            'tipo_asignacion' => 'obligatorio'
        ];

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('La cantidad de horas es inválida.');

        // Act
        $this->carreraService->crearYAsignarCurso($carrera, $data);
    }

    public function testCrearYAsignarCursoWithInvalidTipo(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $data = [
            'nombre' => 'Curso Test',
            'cantidadHoras' => '40',
            'tipo_asignacion' => 'invalido'
        ];

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El tipo de asignación es inválido.');

        // Act
        $this->carreraService->crearYAsignarCurso($carrera, $data);
    }

    public function testEditarInscripcionSuccess(): void
    {
        // Arrange
        // Crear un mock que simule ser InscripcionCarrera
        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $carrera = $this->createMock(Carrera::class);
        $descuento = $this->createMock(Descuento::class);
        
        $data = [
            'descuento' => 1,
            'fechaInscripcion' => '2024-01-15',
            'nroLegajo' => '12345'
        ];
        
        $inscripcion->method('getCarrera')->willReturn($carrera);
        $inscripcion->method('getId')->willReturn(10);
        
        $this->descuentoRepository->method('find')
            ->with(1)
            ->willReturn($descuento);
        
        $this->inscripcionCarreraRepository->method('findOneBy')
            ->with(['carrera' => $carrera, 'nroLegajo' => 12345])
            ->willReturn(null);
        
        $inscripcion->expects($this->once())
            ->method('setDescuento')
            ->with($descuento);
        
        $inscripcion->expects($this->once())
            ->method('setFechaInscripcion')
            ->with($this->callback(function ($value) {
                return $value instanceof \DateTime && $value->format('Y-m-d') === '2024-01-15';
            }));
        
        $inscripcion->expects($this->once())
            ->method('setNroLegajo')
            ->with(12345);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($inscripcion);
        
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->carreraService->editarInscripcion($inscripcion, $data);
    }

    public function testEditarInscripcionWithDuplicateLegajo(): void
    {
        // Arrange
        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $carrera = $this->createMock(Carrera::class);
        $existingInscripcion = $this->createMock(InscripcionCarrera::class);
        
        $data = [
            'nroLegajo' => '12345'
        ];
        
        $inscripcion->method('getCarrera')->willReturn($carrera);
        $inscripcion->method('getId')->willReturn(10);
        
        $existingInscripcion->method('getId')->willReturn(20);
        
        $this->inscripcionCarreraRepository->method('findOneBy')
            ->with(['carrera' => $carrera, 'nroLegajo' => 12345])
            ->willReturn($existingInscripcion);

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El legajo ya existe en la carrera.');

        // Act
        $this->carreraService->editarInscripcion($inscripcion, $data);
    }

    public function testEditarInscripcionWithLegajoToSelf(): void
    {
        // Arrange
        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $carrera = $this->createMock(Carrera::class);
        
        $data = [
            'nroLegajo' => '12345'
        ];
        
        $inscripcion->method('getCarrera')->willReturn($carrera);
        $inscripcion->method('getId')->willReturn(10);
        
        $this->inscripcionCarreraRepository->method('findOneBy')
            ->with(['carrera' => $carrera, 'nroLegajo' => 12345])
            ->willReturn($inscripcion);
        
        $inscripcion->expects($this->once())
            ->method('setNroLegajo')
            ->with(12345);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($inscripcion);
        
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->carreraService->editarInscripcion($inscripcion, $data);
    }

    public function testDesinscribirAlumnoSuccess(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $alumno = $this->createMock(Alumno::class);
        $inscripcion = $this->createMock(InscripcionCarrera::class);
        $cuota1 = $this->createMock(Cuota::class);
        $cuota2 = $this->createMock(Cuota::class);
        
        $this->inscripcionCarreraRepository->method('findOneBy')
            ->with(['carrera' => $carrera, 'alumno' => $alumno])
            ->willReturn($inscripcion);
        
        $cuotaRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $cuotaRepository->method('findBy')
            ->with(['inscripcionCarrera' => $inscripcion])
            ->willReturn([$cuota1, $cuota2]);
        
        $this->entityManager->method('getRepository')
            ->with(Cuota::class)
            ->willReturn($cuotaRepository);
        
        $removeCalls = [];
        $this->entityManager->expects($this->exactly(3))
            ->method('remove')
            ->willReturnCallback(function ($object) use (&$removeCalls) {
                $removeCalls[] = get_class($object);
            });
        
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->carreraService->desinscribirAlumno($carrera, $alumno);

        // Assert
        $this->assertCount(3, $removeCalls);
    }

    public function testDesinscribirAlumnoWhenNotInscribed(): void
    {
        // Arrange
        $carrera = $this->createMock(Carrera::class);
        $alumno = $this->createMock(Alumno::class);
        
        $this->inscripcionCarreraRepository->method('findOneBy')
            ->with(['carrera' => $carrera, 'alumno' => $alumno])
            ->willReturn(null);

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El alumno no está inscrito en esta carrera.');

        // Act
        $this->carreraService->desinscribirAlumno($carrera, $alumno);
    }
}