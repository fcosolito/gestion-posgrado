<?php

namespace App\Tests\Service;

use App\Entity\Comprobante;
use App\Entity\Cuota;
use App\Entity\Pago;
use App\Entity\PagoCuota;
use App\Service\PagoService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PagoServiceTest extends TestCase
{
    private $em;
    private $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->service = new PagoService($this->em);
    }

    /** ---------------------- TEST newComprobante ----------------------- */

    public function testNewComprobanteExito()
    {
        $comprobante = new Comprobante();

        $archivo = $this->createMock(UploadedFile::class);
        $archivo->method('getClientOriginalName')->willReturn('prueba.pdf');
        $archivo->method('guessExtension')->willReturn('pdf');

        // Evitar filesystem real
        $temp = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($temp, 'dummy');
        $archivo->method('move')->willReturn(new File($temp));

        $this->em->expects($this->once())->method('persist')->with($comprobante);
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->newComprobante($comprobante, $archivo, '/fake/path');

        $this->assertEquals('exito', $result['estado']);
        $this->assertInstanceOf(Comprobante::class, $result['comprobante']);
        $this->assertNotEmpty($comprobante->getArchivo());
    }

    /** ---------------------- TEST updateComprobante ----------------------- */

    public function testUpdateComprobanteBorraAnteriorYUsaNewComprobante()
    {
        $comprobante = new Comprobante();
        $archivo = $this->createMock(UploadedFile::class);
        $archivo->method('getClientOriginalName')->willReturn('nuevo.pdf');
        $archivo->method('guessExtension')->willReturn('pdf');
        $temp = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($temp, 'dummy');
        $archivo->method('move')->willReturn(new File($temp));

        $archivoAntiguo = 'viejo.pdf';

        // Mock de filesystem
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('exists')->willReturn(true);
        $filesystem->expects($this->once())->method('remove');

        $result = $this->service->updateComprobante(
            $comprobante, 
            $archivo, 
            $archivoAntiguo, 
            '/fake/path',
            $filesystem
        );

        $this->assertEquals('exito', $result['estado']);
    }

    /** ---------------------- TEST new (crear pago) ----------------------- */

    public function testNewErrorSinCuotas()
    {
        $pago = new Pago();

        $result = $this->service->new($pago, []);

        $this->assertEquals('error', $result['estado']);
    }

    public function testNewCreaPagoYCreaPagoCuotas()
    {
        // Preparar datos
        $pago = new Pago();
        $pago->setMonto(300);

        $cuota1 = new Cuota();
        $cuota2 = new Cuota();
        $cuotas = [$cuota1, $cuota2];

        $this->em->expects($this->exactly(3))->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->new($pago, $cuotas);

        $this->assertEquals('exito', $result['estado']);
        $this->assertSame($pago, $result['pago']);
    }

    /** ---------------------- TEST asociarCuota ----------------------- */

    public function testAsociarCuotaErrorMontoMayorQuePago()
    {
        $pago = new Pago();
        $pago->setMonto(100);

        $cuota = new Cuota();

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($repo);

        $result = $this->service->asociarCuota($pago, $cuota, 200);

        $this->assertEquals('error', $result['estado']);
    }

    public function testAsociarCuotaExito()
    {
        $pago = new Pago();
        $pago->setMonto(300);

        $cuota = new Cuota();

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->asociarCuota($pago, $cuota, 100);

        $this->assertEquals('exito', $result['estado']);
    }

    /** ---------------------- TEST desasociarCuota ----------------------- */

    public function testDesasociarCuotaExito()
    {
        $pago = new Pago();
        $cuota = new Cuota();

        $pagoCuota = new PagoCuota();

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($pagoCuota);

        $this->em->method('getRepository')->willReturn($repo);

        $this->em->expects($this->once())->method('remove')->with($pagoCuota);
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->desasociarCuota($pago, $cuota);

        $this->assertEquals('exito', $result['estado']);
    }

    /** ---------------------- TEST delete (eliminar pago completo) ----------------------- */

    public function testDeleteExito()
    {
        $pago = $this->createMock(Pago::class);
        $comprobante = $this->createMock(Comprobante::class);

        $pagoCuotas = new ArrayCollection([new PagoCuota(), new PagoCuota()]);

        $pago->method('getComprobante')->willReturn($comprobante);
        $pago->method('getPagoCuotas')->willReturn($pagoCuotas);

        $comprobante->method('getArchivo')->willReturn('archivo.pdf');

        $this->em->expects($this->exactly(3))->method('remove');
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->delete($pago, '/fake/path');

        $this->assertEquals('exito', $result['exception']);
    }
}
