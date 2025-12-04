<?php

namespace App\DataFixtures;

use App\Entity\Alumno;
use App\Entity\Carrera;
use App\Entity\Comprobante;
use App\Entity\Cuota;
use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\Dicta;
use App\Entity\Docente;
use App\Entity\DocumentacionNota;
use App\Entity\Edicion;
use App\Entity\InscripcionCarrera;
use App\Entity\InscripcionEdicion;
use App\Entity\Nota;
use App\Entity\Pago;
use App\Entity\PagoCuota;
use App\Entity\PerteneceA;
use App\Entity\PrecioCarrera;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
     public function load(ObjectManager $manager): void
    {
        // Carreras (ampliadas)
        $carreras = [];
        $carreraData = [
            ["Maestría en Ingeniería de Software", 1234, 5678, 12, 5000.00],
            ["Especialización en Data Science", 1235, 5679, 10, 4500.00],
            ["Doctorado en Ciencias de la Computación", 1236, 5680, 24, 8000.00],
            ["Licenciatura en Sistemas", 1237, 5681, 8, 3000.00],
            ["Tecnicatura en Desarrollo Web", 1238, 5682, 6, 2000.00],
            ["Maestría en Inteligencia Artificial", 1239, 5683, 18, 6000.00],
        ];

        foreach ($carreraData as $data) {
            $carrera = new Carrera();
            $carrera->setNombre($data[0]);
            $carrera->setNroOrdenanza($data[1]);
            $carrera->setNroImplementacion($data[2]);
            $carrera->setCantidadCuotas($data[3]);
            $carrera->setPrecioInscripcion($data[4]);
            $manager->persist($carrera);
            $carreras[] = $carrera;
        }

        // Precios de Carrera (ampliados)
        $precioCarreraData = [
            [$carreras[0], 120000.00, '2024-01-01'],
            [$carreras[0], 135000.00, '2024-06-01'],
            [$carreras[1], 95000.00, '2024-01-01'],
            [$carreras[1], 105000.00, '2024-07-01'],
            [$carreras[2], 180000.00, '2024-01-01'],
            [$carreras[3], 80000.00, '2024-01-01'],
            [$carreras[4], 50000.00, '2024-01-01'],
            [$carreras[5], 150000.00, '2024-01-01'],
        ];

        foreach ($precioCarreraData as $i => $data) {
            $precioCarrera = new PrecioCarrera();
            $precioCarrera->setCarrera($data[0]);
            $precioCarrera->setPrecio($data[1]);
            $precioCarrera->setFechaVigencia(new DateTime($data[2]));
            $precioCarrera->setFechaCreacion(new DateTime($data[2] . ' ' . ($i + 9) . ':00:00'));
            $manager->persist($precioCarrera);
        }

        // Cursos (ampliados)
        $cursos = [];
        $cursoData = [
            ["Matemática Avanzada", 2001, 1001, 60],
            ["Programación en Python", 2002, 1002, 80],
            ["Machine Learning", 2003, 1003, 100],
            ["Bases de Datos", 2004, 1004, 70],
            ["Estadística Aplicada", 2005, 1005, 50],
            ["Desarrollo Web Full Stack", 2006, 1006, 90],
            ["Redes y Comunicaciones", 2007, 1007, 65],
            ["Seguridad Informática", 2008, 1008, 75],
            ["Cloud Computing", 2009, 1009, 85],
            ["Big Data Analytics", 2010, 1010, 95],
        ];

        foreach ($cursoData as $data) {
            $curso = new Curso();
            $curso->setNombre($data[0]);
            $curso->setNroOrdenanza($data[1]);
            $curso->setNroImplementacion($data[2]);
            $curso->setHoras($data[3]);
            $manager->persist($curso);
            $cursos[] = $curso;
        }

        // Alumnos (ampliados)
        $alumnos = [];
        $alumnoData = [
            ["Franco", "Cosolito", 30123456, "franco@mail.com"],
            ["Enzo", "Garello", 32123456, "enzo@mail.com"],
            ["María", "Gómez", 34123456, "maria.gomez@email.com"],
            ["Carlos", "López", 36123456, "carlos.lopez@email.com"],
            ["Ana", "Martínez", 38123456, "ana.martinez@email.com"],
            ["Lucía", "Rodríguez", 40123456, "lucia.rodriguez@email.com"],
            ["Diego", "Fernández", 42123456, "diego.fernandez@email.com"],
            ["Sofía", "Pérez", 44123456, "sofia.perez@email.com"],
            ["Javier", "García", 46123456, "javier.garcia@email.com"],
            ["Laura", "Silva", 48123456, "laura.silva@email.com"],
            ["Miguel", "Torres", 50123456, "miguel.torres@email.com"],
            ["Elena", "Ramírez", 52123456, "elena.ramirez@email.com"],
        ];

        foreach ($alumnoData as $data) {
            $alumno = new Alumno();
            $alumno->setNombre($data[0]);
            $alumno->setApellido($data[1]);
            $alumno->setDni($data[2]);
            $alumno->setEmail($data[3]);
            $manager->persist($alumno);
            $alumnos[] = $alumno;
        }

        // Docentes (nuevos)
        $docentes = [];
        $docenteData = [
            ["Roberto", "González", 20123456, "roberto.gonzalez@email.com"],
            ["Patricia", "Mendoza", 21123456, "patricia.mendoza@email.com"],
            ["Alejandro", "Suárez", 22123456, "alejandro.suarez@email.com"],
            ["Claudia", "Ríos", 23123456, "claudia.rios@email.com"],
            ["Ricardo", "Vargas", 24123456, "ricardo.vargas@email.com"],
        ];

        foreach ($docenteData as $data) {
            $docente = new Docente();
            $docente->setNombre($data[0]);
            $docente->setApellido($data[1]);
            $docente->setDni($data[2]);
            $docente->setEmail($data[3]);
            $manager->persist($docente);
            $docentes[] = $docente;
        }

        // PerteneceA - Asignar cursos a carreras (ampliado)
        $perteneceData = [
            [$cursos[0], $carreras[0], false],
            [$cursos[1], $carreras[0], false],
            [$cursos[2], $carreras[1], false],
            [$cursos[3], $carreras[0], true],
            [$cursos[4], $carreras[1], true],
            [$cursos[5], $carreras[3], false],
            [$cursos[6], $carreras[0], true],
            [$cursos[7], $carreras[2], false],
            [$cursos[8], $carreras[5], false],
            [$cursos[9], $carreras[1], false],
            [$cursos[5], $carreras[4], false],
            [$cursos[1], $carreras[3], false],
        ];

        foreach ($perteneceData as $data) {
            $pertenece = new PerteneceA();
            $pertenece->setCurso($data[0]);
            $pertenece->setCarrera($data[1]);
            $pertenece->setEsElectivo($data[2]);
            $manager->persist($pertenece);
        }

        // Ediciones (ampliadas)
        $ediciones = [];
        $edicionData = [
            [$cursos[0], "Matemática Avanzada 2024-1", '2024-03-01', '2024-07-01', 15000.0],
            [$cursos[1], "Programación Python 2024-1", '2024-04-01', '2024-08-01', 18000.0],
            [$cursos[2], "Machine Learning 2024-2", '2024-08-01', '2024-12-01', 25000.0],
            [$cursos[3], "Bases de Datos 2024-1", '2024-03-15', '2024-07-15', 16000.0],
            [$cursos[4], "Estadística Aplicada 2024-1", '2024-02-01', '2024-06-01', 14000.0],
            [$cursos[5], "Desarrollo Web 2024-1", '2024-05-01', '2024-09-01', 20000.0],
            [$cursos[6], "Redes 2024-2", '2024-09-01', '2025-01-01', 17000.0],
            [$cursos[7], "Seguridad 2024-2", '2024-10-01', '2025-02-01', 22000.0],
        ];

        foreach ($edicionData as $data) {
            $edicion = new Edicion();
            $edicion->setCurso($data[0]);
            $edicion->setNombre($data[1]);
            $edicion->setFechaInicio(new DateTime($data[2]));
            $edicion->setFechaFin(new DateTime($data[3]));
            $edicion->setPrecio($data[4]);
            $manager->persist($edicion);
            $ediciones[] = $edicion;
        }

        // Dicta - Asignar docentes a ediciones
        $dictaData = [
            [$ediciones[0], $docentes[0], true],
            [$ediciones[0], $docentes[1], false],
            [$ediciones[1], $docentes[2], true],
            [$ediciones[2], $docentes[3], true],
            [$ediciones[3], $docentes[4], true],
            [$ediciones[4], $docentes[0], true],
            [$ediciones[5], $docentes[1], true],
            [$ediciones[6], $docentes[2], true],
            [$ediciones[7], $docentes[3], true],
        ];

        foreach ($dictaData as $data) {
            $dicta = new Dicta();
            $dicta->setEdicion($data[0]);
            $dicta->setDocente($data[1]);
            $dicta->setEsFirmante($data[2]);
            $manager->persist($dicta);
        }

        // Descuentos (ampliados)
        $descuentos = [];
        $descuentoData = [
            ["Beca por excelencia académica", 20.00],
            ["Descuento por pago anticipado", 10.00],
            ["Beca deportiva", 15.00],
            ["Beca por situación económica", 25.00],
            ["Descuento por grupo familiar", 30.00],
            ["Beca por investigación", 40.00],
            ["Descuento por convenio empresarial", 20.00],
        ];

        foreach ($descuentoData as $data) {
            $descuento = new Descuento();
            $descuento->setDescripcion($data[0]);
            $descuento->setValor($data[1]);
            $manager->persist($descuento);
            $descuentos[] = $descuento;
        }

        // Comprobantes (nuevos)
        $comprobantes = [];
        for ($i = 1; $i <= 20; $i++) {
            $comprobante = new Comprobante();
            $comprobante->setArchivo("comprobante_$i.pdf");
            $manager->persist($comprobante);
            $comprobantes[] = $comprobante;
        }

        // Documentación de Notas (nuevos)
        $documentacionesNota = [];
        for ($i = 1; $i <= 15; $i++) {
            $docNota = new DocumentacionNota();
            $docNota->setArchivo("documentacion_nota_$i.pdf");
            $manager->persist($docNota);
            $documentacionesNota[] = $docNota;
        }

        // Inscripciones a Carreras (ampliadas)
        $inscCarreras = [];
        $inscCarreraData = [
            [$alumnos[0], $carreras[0], $descuentos[0]],
            [$alumnos[1], $carreras[0], $descuentos[1]],
            [$alumnos[2], $carreras[1], $descuentos[2]],
            [$alumnos[3], $carreras[2], $descuentos[3]],
            [$alumnos[4], $carreras[3], $descuentos[4]],
            [$alumnos[5], $carreras[4], $descuentos[5]],
            [$alumnos[6], $carreras[5], $descuentos[6]],
            [$alumnos[7], $carreras[0], null],
            [$alumnos[8], $carreras[1], $descuentos[1]],
            [$alumnos[9], $carreras[2], $descuentos[2]],
        ];

        foreach ($inscCarreraData as $data) {
            $inscCarrera = new InscripcionCarrera();
            $inscCarrera->setAlumno($data[0]);
            $inscCarrera->setCarrera($data[1]);
            $inscCarrera->setDescuento($data[2]);
            $manager->persist($inscCarrera);
            $inscCarreras[] = $inscCarrera;
        }

        // Inscripciones a Ediciones (ampliadas)
        $inscEdiciones = [];
        $inscEdicionData = [
            [$alumnos[0], $ediciones[0], $descuentos[0]],
            [$alumnos[1], $ediciones[0], $descuentos[1]],
            [$alumnos[0], $ediciones[1], null],
            [$alumnos[2], $ediciones[1], $descuentos[2]],
            [$alumnos[3], $ediciones[2], $descuentos[3]],
            [$alumnos[4], $ediciones[3], $descuentos[4]],
            [$alumnos[5], $ediciones[4], $descuentos[5]],
            [$alumnos[6], $ediciones[5], $descuentos[6]],
            [$alumnos[7], $ediciones[6], null],
            [$alumnos[8], $ediciones[7], $descuentos[1]],
            [$alumnos[9], $ediciones[0], $descuentos[2]],
            [$alumnos[10], $ediciones[1], $descuentos[3]],
            [$alumnos[11], $ediciones[2], $descuentos[4]],
        ];

        foreach ($inscEdicionData as $data) {
            $inscEdicion = new InscripcionEdicion();
            $inscEdicion->setAlumno($data[0]);
            $inscEdicion->setEdicion($data[1]);
            $inscEdicion->setDescuento($data[2]);
            $manager->persist($inscEdicion);
            $inscEdiciones[] = $inscEdicion;
        }

        // Notas (ampliadas)
        $notas = [];
        $notaData = [
            [$inscEdiciones[0], 8.5, '2024-07-15', "Examen final", $documentacionesNota[0]],
            [$inscEdiciones[1], 9.0, '2024-07-15', "Examen final", $documentacionesNota[1]],
            [$inscEdiciones[2], 7.5, '2024-08-20', "Proyecto integrador", $documentacionesNota[2]],
            [$inscEdiciones[3], 8.0, '2024-08-20', "Examen práctico", $documentacionesNota[3]],
            [$inscEdiciones[4], 9.5, '2024-12-10', "Trabajo final", $documentacionesNota[4]],
            [$inscEdiciones[5], 6.5, '2024-07-20', "Examen parcial", null],
            [$inscEdiciones[6], 8.8, '2024-06-15', "Evaluación continua", $documentacionesNota[5]],
            [$inscEdiciones[7], 7.0, '2025-01-20', "Examen final", null],
            [$inscEdiciones[8], 9.2, '2025-01-20', "Proyecto final", $documentacionesNota[6]],
        ];

        foreach ($notaData as $i => $data) {
            $nota = new Nota();
            $nota->setValor($data[1]);
            $nota->setInscripcionEdicion($data[0]);
            $nota->setFechaCarga(new DateTime($data[2]));
            $nota->setDescripcion($data[3]);
            $nota->setDocumentacionNota($data[4]);
            $manager->persist($nota);
            $notas[] = $nota;
        }

        // Cuotas (ampliadas)
        $cuotas = [];
        
        // Cuotas para inscripciones a ediciones
        foreach ($inscEdiciones as $inscEdicion) {
            for ($i = 1; $i <= 3; $i++) {
                $cuota = new Cuota();
                $cuota->setInscripcionEdicion($inscEdicion);
                $cuota->setNumeroCuota($i);
                $manager->persist($cuota);
                $cuotas[] = $cuota;
            }
        }

        // Cuotas para inscripciones a carreras
        foreach ($inscCarreras as $inscCarrera) {
            $carrera = $inscCarrera->getCarrera();
            $cantidadCuotas = $carrera->getCantidadCuotas() ?? 6;
            for ($i = 1; $i <= min(4, $cantidadCuotas); $i++) {
                $cuota = new Cuota();
                $cuota->setInscripcionCarrera($inscCarrera);
                $cuota->setNumeroCuota($i);
                $manager->persist($cuota);
                $cuotas[] = $cuota;
            }
        }

        // Pagos (ampliados)
        $pagos = [];
        $pagoData = [
            [15000, '2024-03-05', $comprobantes[0]],
            [18000, '2024-04-10', $comprobantes[1]],
            [16000, '2024-03-20', $comprobantes[2]],
            [14000, '2024-02-15', $comprobantes[3]],
            [20000, '2024-05-12', $comprobantes[4]],
            [17000, '2024-09-10', $comprobantes[5]],
            [22000, '2024-10-05', $comprobantes[6]],
            [5000, '2024-01-20', $comprobantes[7]],
            [4500, '2024-02-01', $comprobantes[8]],
            [8000, '2024-01-25', $comprobantes[9]],
            [3000, '2024-03-01', $comprobantes[10]],
            [6000, '2024-02-10', $comprobantes[11]],
        ];

        foreach ($pagoData as $data) {
            $pago = new Pago();
            $pago->setMonto($data[0]);
            $pago->setFechaPago(new DateTime($data[1]));
            $pago->setComprobante($data[2]);
            $manager->persist($pago);
            $pagos[] = $pago;
        }

        // PagoCuota (nuevos)
        $pagoCuotaData = [
            [$cuotas[0], $pagos[0], 5000],
            [$cuotas[1], $pagos[0], 5000],
            [$cuotas[2], $pagos[0], 5000],
            [$cuotas[3], $pagos[1], 6000],
            [$cuotas[4], $pagos[1], 6000],
            [$cuotas[5], $pagos[1], 6000],
            [$cuotas[6], $pagos[2], 16000],
            [$cuotas[15], $pagos[7], 5000],
            [$cuotas[16], $pagos[8], 4500],
            [$cuotas[17], $pagos[9], 8000],
        ];

        foreach ($pagoCuotaData as $data) {
            $pagoCuota = new PagoCuota();
            $pagoCuota->setCuota($data[0]);
            $pagoCuota->setPago($data[1]);
            $pagoCuota->setMontoCuota($data[2]);
            $manager->persist($pagoCuota);
        }

        // Persistir cambios
        $manager->flush();
    }
}