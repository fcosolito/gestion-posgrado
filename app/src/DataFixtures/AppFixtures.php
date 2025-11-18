<?php

namespace App\DataFixtures;

use App\Entity\Alumno;
use App\Entity\Carrera;
use App\Entity\Cuota;
use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\Edicion;
use App\Entity\InscripcionCarrera;
use App\Entity\InscripcionEdicion;
use App\Entity\Nota;
use App\Entity\Pago;
use App\Entity\PerteneceA;
use App\Entity\PrecioCarrera;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Carreras
        $carreras = [];
        $carrera1 = new Carrera();
        $carrera1->setNombre("Maestría en Ingeniería de Software");
        $carrera1->setNroImplementacion(5678);
        $carrera1->setNroOrdenanza(1234);
        $carrera1->setCantidadCuotas(12);
        $carrera1->setPrecioInscripcion(5000.00);
        $manager->persist($carrera1);
        $carreras[] = $carrera1;

        $carrera2 = new Carrera();
        $carrera2->setNombre("Especialización en Data Science");
        $carrera2->setNroImplementacion(5679);
        $carrera2->setNroOrdenanza(1235);
        $carrera2->setCantidadCuotas(10);
        $carrera2->setPrecioInscripcion(4500.00);
        $manager->persist($carrera2);
        $carreras[] = $carrera2;

        $carrera3 = new Carrera();
        $carrera3->setNombre("Doctorado en Ciencias de la Computación");
        $carrera3->setNroImplementacion(5680);
        $carrera3->setNroOrdenanza(1236);
        $carrera3->setCantidadCuotas(24);
        $carrera3->setPrecioInscripcion(8000.00);
        $manager->persist($carrera3);
        $carreras[] = $carrera3;

        // Precios de Carrera
        $precioCarrera1 = new PrecioCarrera();
        $precioCarrera1->setCarrera($carrera1);
        $precioCarrera1->setPrecio(120000.00);
        $precioCarrera1->setFechaVigencia(new DateTime('2024-01-01'));
        $precioCarrera1->setFechaCreacion(new DateTime('2024-01-01 10:00:00'));
        $manager->persist($precioCarrera1);

        $precioCarrera2 = new PrecioCarrera();
        $precioCarrera2->setCarrera($carrera1);
        $precioCarrera2->setPrecio(135000.00);
        $precioCarrera2->setFechaVigencia(new DateTime('2024-06-01'));
        $precioCarrera2->setFechaCreacion(new DateTime('2024-05-15 09:30:00'));
        $manager->persist($precioCarrera2);

        $precioCarrera3 = new PrecioCarrera();
        $precioCarrera3->setCarrera($carrera2);
        $precioCarrera3->setPrecio(95000.00);
        $precioCarrera3->setFechaVigencia(new DateTime('2024-01-01'));
        $precioCarrera3->setFechaCreacion(new DateTime('2024-01-01 11:00:00'));
        $manager->persist($precioCarrera3);

        // Cursos
        $cursos = [];
        $curso1 = new Curso();
        $curso1->setNombre("Matemática Avanzada");
        $curso1->setNroImplementacion(1001);
        $curso1->setNroOrdenanza(2001);
        $curso1->setHoras(60);
        $manager->persist($curso1);
        $cursos[] = $curso1;

        $curso2 = new Curso();
        $curso2->setNombre("Programación en Python");
        $curso2->setNroImplementacion(1002);
        $curso2->setNroOrdenanza(2002);
        $curso2->setHoras(80);
        $manager->persist($curso2);
        $cursos[] = $curso2;

        $curso3 = new Curso();
        $curso3->setNombre("Machine Learning");
        $curso3->setNroImplementacion(1003);
        $curso3->setNroOrdenanza(2003);
        $curso3->setHoras(100);
        $manager->persist($curso3);
        $cursos[] = $curso3;

        $curso4 = new Curso();
        $curso4->setNombre("Bases de Datos");
        $curso4->setNroImplementacion(1004);
        $curso4->setNroOrdenanza(2004);
        $curso4->setHoras(70);
        $manager->persist($curso4);
        $cursos[] = $curso4;

        $curso5 = new Curso();
        $curso5->setNombre("Estadística Aplicada");
        $curso5->setNroImplementacion(1005);
        $curso5->setNroOrdenanza(2005);
        $curso5->setHoras(50);
        $manager->persist($curso5);
        $cursos[] = $curso5;

        // Alumnos
        $alumnos = [];
        $alumno1 = new Alumno();
        $alumno1->setNombre("Franco");
        $alumno1->setApellido("Cosolito");
        $alumno1->setDni(30123456);
        $alumno1->setEmail("franco@mail.com");
        $manager->persist($alumno1);
        $alumnos[] = $alumno1;

        $alumno2 = new Alumno();
        $alumno2->setNombre("Enzo");
        $alumno2->setApellido("Garello");
        $alumno2->setDni(32123456);
        $alumno2->setEmail("enzo@mail.com");
        $manager->persist($alumno2);
        $alumnos[] = $alumno2;

        $alumno3 = new Alumno();
        $alumno3->setNombre("María");
        $alumno3->setApellido("Gómez");
        $alumno3->setDni(34123456);
        $alumno3->setEmail("maria.gomez@email.com");
        $manager->persist($alumno3);
        $alumnos[] = $alumno3;

        $alumno4 = new Alumno();
        $alumno4->setNombre("Carlos");
        $alumno4->setApellido("López");
        $alumno4->setDni(36123456);
        $alumno4->setEmail("carlos.lopez@email.com");
        $manager->persist($alumno4);
        $alumnos[] = $alumno4;

        $alumno5 = new Alumno();
        $alumno5->setNombre("Ana");
        $alumno5->setApellido("Martínez");
        $alumno5->setDni(38123456);
        $alumno5->setEmail("ana.martinez@email.com");
        $manager->persist($alumno5);
        $alumnos[] = $alumno5;

        // PerteneceA - Asignar cursos a carreras
        $pertenece1 = new PerteneceA();
        $pertenece1->setCurso($curso1);
        $pertenece1->setCarrera($carrera1);
        $pertenece1->setEsElectivo(false);
        $manager->persist($pertenece1);

        $pertenece2 = new PerteneceA();
        $pertenece2->setCurso($curso2);
        $pertenece2->setCarrera($carrera1);
        $pertenece2->setEsElectivo(false);
        $manager->persist($pertenece2);

        $pertenece3 = new PerteneceA();
        $pertenece3->setCurso($curso3);
        $pertenece3->setCarrera($carrera2);
        $pertenece3->setEsElectivo(false);
        $manager->persist($pertenece3);

        $pertenece4 = new PerteneceA();
        $pertenece4->setCurso($curso4);
        $pertenece4->setCarrera($carrera1);
        $pertenece4->setEsElectivo(true);
        $manager->persist($pertenece4);

        $pertenece5 = new PerteneceA();
        $pertenece5->setCurso($curso5);
        $pertenece5->setCarrera($carrera2);
        $pertenece5->setEsElectivo(true);
        $manager->persist($pertenece5);

        // Ediciones
        $ediciones = [];
        $edicion1 = new Edicion();
        $edicion1->setCurso($curso1);
        $edicion1->setNombre("Matemática Avanzada 2024-1");
        $edicion1->setFechaInicio(new DateTime('2024-03-01'));
        $edicion1->setFechaFin(new DateTime('2024-07-01'));
        $edicion1->setPrecio(15000.0);
        $manager->persist($edicion1);
        $ediciones[] = $edicion1;

        $edicion2 = new Edicion();
        $edicion2->setCurso($curso2);
        $edicion2->setNombre("Programación Python 2024-1");
        $edicion2->setFechaInicio(new DateTime('2024-04-01'));
        $edicion2->setFechaFin(new DateTime('2024-08-01'));
        $edicion2->setPrecio(18000.0);
        $manager->persist($edicion2);
        $ediciones[] = $edicion2;

        $edicion3 = new Edicion();
        $edicion3->setCurso($curso3);
        $edicion3->setNombre("Machine Learning 2024-2");
        $edicion3->setFechaInicio(new DateTime('2024-08-01'));
        $edicion3->setFechaFin(new DateTime('2024-12-01'));
        $edicion3->setPrecio(25000.0);
        $manager->persist($edicion3);
        $ediciones[] = $edicion3;

        // Descuentos
        $descuentos = [];
        $descuento1 = new Descuento();
        $descuento1->setDescripcion("Beca por excelencia académica");
        $descuento1->setValor(20.00);
        $manager->persist($descuento1);
        $descuentos[] = $descuento1;

        $descuento2 = new Descuento();
        $descuento2->setDescripcion("Descuento por pago anticipado");
        $descuento2->setValor(10.00);
        $manager->persist($descuento2);
        $descuentos[] = $descuento2;

        $descuento3 = new Descuento();
        $descuento3->setDescripcion("Beca deportiva");
        $descuento3->setValor(15.00);
        $manager->persist($descuento3);
        $descuentos[] = $descuento3;

        // Inscripciones a Carreras
        $inscCarrera1 = new InscripcionCarrera();
        $inscCarrera1->setAlumno($alumno1);
        $inscCarrera1->setCarrera($carrera1);
        $inscCarrera1->setDescuento($descuento1);
        $manager->persist($inscCarrera1);

        $inscCarrera2 = new InscripcionCarrera();
        $inscCarrera2->setAlumno($alumno2);
        $inscCarrera2->setCarrera($carrera1);
        $inscCarrera2->setDescuento($descuento2);
        $manager->persist($inscCarrera2);

        $inscCarrera3 = new InscripcionCarrera();
        $inscCarrera3->setAlumno($alumno3);
        $inscCarrera3->setCarrera($carrera2);
        $inscCarrera3->setDescuento($descuento3);
        $manager->persist($inscCarrera3);

        // Inscripciones a Ediciones
        $inscEdicion1 = new InscripcionEdicion();
        $inscEdicion1->setAlumno($alumno1);
        $inscEdicion1->setEdicion($edicion1);
        $inscEdicion1->setDescuento($descuento1);
        $manager->persist($inscEdicion1);

        $inscEdicion2 = new InscripcionEdicion();
        $inscEdicion2->setAlumno($alumno2);
        $inscEdicion2->setEdicion($edicion1);
        $inscEdicion2->setDescuento($descuento2);
        $manager->persist($inscEdicion2);

        $inscEdicion3 = new InscripcionEdicion();
        $inscEdicion3->setAlumno($alumno1);
        $inscEdicion3->setEdicion($edicion2);
        $inscEdicion3->setDescuento(null);
        $manager->persist($inscEdicion3);

        // Notas
        $nota1 = new Nota();
        $nota1->setValor(8.5);
        $nota1->setInscripcionEdicion($inscEdicion1);
        $nota1->setFechaCarga(new DateTime('2024-07-15'));
        $nota1->setDescripcion("Examen final");
        $inscEdicion1->setNota($nota1);
        $manager->persist($nota1);

        $nota2 = new Nota();
        $nota2->setValor(9.0);
        $nota2->setInscripcionEdicion($inscEdicion2);
        $nota2->setFechaCarga(new DateTime('2024-07-15'));
        $nota2->setDescripcion("Examen final");
        $inscEdicion2->setNota($nota2);
        $manager->persist($nota2);

        // Cuotas
        $cuota1 = new Cuota();
        $cuota1->setInscripcionEdicion($inscEdicion1);
        $cuota1->setNumeroCuota(1);
        $manager->persist($cuota1);

        $cuota2 = new Cuota();
        $cuota2->setInscripcionEdicion($inscEdicion1);
        $cuota2->setNumeroCuota(2);
        $manager->persist($cuota2);

        $cuota3 = new Cuota();
        $cuota3->setInscripcionCarrera($inscCarrera1);
        $cuota3->setNumeroCuota(1);
        $manager->persist($cuota3);

        // Pagos
        $pago1 = new Pago();
        $pago1->setMonto(15000);
        $pago1->setFechaPago(new DateTime('2024-03-05'));
        $manager->persist($pago1);

        $pago2 = new Pago();
        $pago2->setMonto(18000);
        $pago2->setFechaPago(new DateTime('2024-04-10'));
        $manager->persist($pago2);

        // Persistir cambios
        $manager->flush();
    }
}