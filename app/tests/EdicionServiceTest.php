<?php

namespace App\Tests\Service;

use App\Entity\Alumno;
use App\Entity\Cuota;
use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\Dicta;
use App\Entity\Docente;
use App\Entity\Edicion;
use App\Entity\InscripcionEdicion;
use App\Entity\Nota;
use App\Entity\PagoCuota;
use App\Service\EdicionService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class EdicionServiceTest extends TestCase
{
    private $em;
    private $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->service = new EdicionService($this->em);
    }

    private function mockRepo(array $methods = [])
    {
        $repo = $this->createMock(EntityRepository::class);
        foreach ($methods as $method => $return) {
            $repo->method($method)->willReturn($return);
        }
        return $repo;
    }

    /** ---------------------------------------
     *  new()
     * --------------------------------------*/
    public function testNewEdicionSuccess()
    {
        $edicion = new Edicion();
        $curso = new Curso();

        $this->em->expects($this->once())->method('persist')->with($edicion);
        $this->em->expects($this->once())->method('flush');

        $resp = $this->service->new($edicion, $curso);

        $this->assertEquals("exito", $resp["estado"]);
        $this->assertNull($resp["edicionId"]); // ID null porque no se usa BD real
    }

    public function testNewEdicionException()
    {
        $edicion = new Edicion();
        $curso = new Curso();

        $this->em->method("persist")->willThrowException(new \Exception("errorX"));

        $resp = $this->service->new($edicion, $curso);

        $this->assertEquals("error", $resp["estado"]);
        $this->assertEquals("errorX", $resp["exception"]);
    }

    /** ---------------------------------------
     *  update()
     * --------------------------------------*/
    public function testUpdateEdicionSuccess()
    {
        $edicion = new Edicion();

        $this->em->expects($this->once())->method('flush');

        $resp = $this->service->update($edicion, [
            "nombre" => "Nueva",
            "fechaInicio" => "2023-01-01",
            "fechaFin" => "2023-12-20",
            "precio" => 1000
        ]);

        $this->assertEquals("exito", $resp["estado"]);
    }

    public function testUpdateEdicionFails()
    {
        $edicion = new Edicion();

        $this->em->method("flush")->willThrowException(new \Exception("X"));

        $resp = $this->service->update($edicion, ["nombre" => "Nueva"]);
        $this->assertEquals("error", $resp["estado"]);
    }

    /** ---------------------------------------
     *  delete()
     * --------------------------------------*/
    public function testDeleteEdicionCascade()
    {
        $edicion = new Edicion();

        $insc1 = new InscripcionEdicion();
        $insc2 = new InscripcionEdicion();
        $nota1 = new Nota();
        $cuota1 = new Cuota();
        $pc1 = new PagoCuota();
        $dicta1 = new Dicta();

        // repos
        $inscRepo = $this->mockRepo(["findBy" => [$insc1, $insc2]]);
        $cuotaRepo = $this->mockRepo(["findBy" => [$cuota1]]);
        $pagoCuotaRepo = $this->mockRepo(["findBy" => [$pc1]]);
        $notaRepo = $this->mockRepo(["findBy" => [$nota1]]);
        $dictaRepo = $this->mockRepo(["findBy" => [$dicta1]]);

        $this->em->method("getRepository")->willReturnMap([
            [InscripcionEdicion::class, $inscRepo],
            [Cuota::class, $cuotaRepo],
            [PagoCuota::class, $pagoCuotaRepo],
            [Nota::class, $notaRepo],
            [Dicta::class, $dictaRepo],
        ]);

        // 1 edicion, 2 insc, 2 cuotas, 2 pagocuota, 2 notas, 1 dicta
        $this->em->expects($this->exactly(1 + 2 + 2 + 2 + 2 + 1)) 
            ->method("remove");

        $this->em->expects($this->once())->method("flush");

        $resp = $this->service->delete($edicion);

        $this->assertEquals("exito", $resp["estado"]);
    }

    /** ---------------------------------------
     *  asociarDocente()
     * --------------------------------------*/
    public function testAsociarDocenteCreatesNew()
    {
        $edicion = new Edicion();
        $docente = new Docente();

        $repo = $this->mockRepo(["findOneBy" => null]);
        $this->em->method("getRepository")->willReturn($repo);

        $this->em->expects($this->once())->method("persist");
        $this->em->expects($this->once())->method("flush");

        $resp = $this->service->asociarDocente($edicion, $docente, true);

        $this->assertEquals("exito", $resp["estado"]);
        $this->assertInstanceOf(Dicta::class, $resp["dicta"]);
    }

    public function testAsociarDocenteException()
    {
        $edicion = new Edicion();
        $docente = new Docente();

        $repo = $this->mockRepo(["findOneBy" => null]);
        $this->em->method("getRepository")->willReturn($repo);

        $this->em->method("flush")->willThrowException(new \Exception("err"));

        $resp = $this->service->asociarDocente($edicion, $docente, true);

        $this->assertEquals("error", $resp["estado"]);
    }

    /** ---------------------------------------
     *  desasociarDocente()
     * --------------------------------------*/
    public function testDesasociarDocente()
    {
        $edicion = new Edicion();
        $docente = new Docente();
        $dicta = new Dicta();

        $repo = $this->mockRepo(["findOneBy" => $dicta]);
        $this->em->method("getRepository")->willReturn($repo);

        $this->em->expects($this->once())->method("remove")->with($dicta);
        $this->em->expects($this->once())->method("flush");

        $resp = $this->service->desasociarDocente($edicion, $docente);

        $this->assertEquals("exito", $resp["estado"]);
    }

    /** ---------------------------------------
     *  inscribirAlumno()
     * --------------------------------------*/
    public function testInscribirAlumnoSuccess()
    {
        $alumno = new Alumno();
        $edicion = new Edicion();

        $descuento = new Descuento();
        $inscripcion = new InscripcionEdicion();

        $repoDesc = $this->mockRepo(["find" => $descuento]);
        $repoIns = $this->mockRepo(["findOneBy" => null]);

        $this->em->method("getRepository")->willReturnMap([
            [Descuento::class, $repoDesc],
            [InscripcionEdicion::class, $repoIns],
        ]);

        $this->em->expects($this->exactly(2))->method("persist");
        $this->em->expects($this->once())->method("flush");

        $resp = $this->service->inscribirAlumno($alumno, $edicion, [
            "descuento" => 1,
            "fechaInscripcion" => "2024-01-01",
            "nroLegajo" => null
        ]);

        $this->assertEquals("exito", $resp["estado"]);
    }

    public function testInscribirAlumnoLegajoDuplicado()
    {
        $alumno = new Alumno();
        $edicion = new Edicion();

        $inscExisting = new InscripcionEdicion();

        $repoDesc = $this->mockRepo(["find" => null]);
        $repoIns = $this->mockRepo(["findOneBy" => $inscExisting]);

        $this->em->method("getRepository")->willReturnMap([
            [Descuento::class, $repoDesc],
            [InscripcionEdicion::class, $repoIns],
        ]);

        $resp = $this->service->inscribirAlumno($alumno, $edicion, [
            "descuento" => null,
            "fechaInscripcion" => "2024-01-05",
            "nroLegajo" => 123
        ]);

        $this->assertEquals("error", $resp["estado"]);
    }

    /** ---------------------------------------
     *  desinscribirAlumno()
     * --------------------------------------*/
    public function testDesinscribirAlumnoSuccess()
    {
        $alumno = new Alumno();
        $edicion = new Edicion();

        $inscripcion = new InscripcionEdicion();
        $cuota = new Cuota();

        $repoIns = $this->mockRepo(["findOneBy" => $inscripcion]);
        $repoCuota = $this->mockRepo(["findOneBy" => $cuota]);

        $this->em->method("getRepository")->willReturnMap([
            [InscripcionEdicion::class, $repoIns],
            [Cuota::class, $repoCuota]
        ]);

        $this->em->expects($this->exactly(2))->method("remove");
        $this->em->expects($this->once())->method("flush");

        $resp = $this->service->desinscribirAlumno($alumno, $edicion);

        $this->assertEquals("exito", $resp["estado"]);
    }

    /** ---------------------------------------
     *  editarInscripcion()
     * --------------------------------------*/
    public function testEditarInscripcionSuccess()
    {
        $inscripcion = new InscripcionEdicion();
        $edicion = new Edicion();
        $desc = new Descuento();

        $repoDesc = $this->mockRepo(["find" => $desc]);
        $repoIns = $this->mockRepo(["findOneBy" => null]);

        $this->em->method("getRepository")->willReturnMap([
            [Descuento::class, $repoDesc],
            [InscripcionEdicion::class, $repoIns]
        ]);

        $this->em->expects($this->once())->method("persist")->with($inscripcion);
        $this->em->expects($this->once())->method("flush");

        $resp = $this->service->editarInscripcion($inscripcion, $edicion, [
            "descuento" => 1,
            "fechaInscripcion" => "2024-02-01",
            "nroLegajo" => 999,
        ]);

        $this->assertEquals("exito", $resp["estado"]);
    }

    public function testEditarInscripcionLegajoDuplicado()
    {
        $inscripcion = new InscripcionEdicion();
        $edicion = new Edicion();

        $repoDesc = $this->mockRepo(["find" => null]);
        $repoIns = $this->mockRepo(["findOneBy" => new InscripcionEdicion()]);

        $this->em->method("getRepository")->willReturnMap([
            [Descuento::class, $repoDesc],
            [InscripcionEdicion::class, $repoIns]
        ]);

        $resp = $this->service->editarInscripcion($inscripcion, $edicion, [
            "descuento" => null,
            "fechaInscripcion" => null,
            "nroLegajo" => 1,
        ]);

        $this->assertEquals("error", $resp["estado"]);
    }
}
