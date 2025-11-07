<?php

namespace App\DataFixtures;

use App\Entity\Alumno;
use App\Entity\Carrera;
use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\Edicion;
use App\Entity\InscripcionEdicion;
use App\Entity\Nota;
use App\Entity\PerteneceA;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Carreras
        $carrera1 = new Carrera();
        $carrera1->setNombre("Sistemas");
        $carrera1->setNroImplementacion(123);
        $carrera1->setNroOrdenanza(123);

        $manager->persist($carrera1);

        // Cursos
        $curso1 = new Curso();
        $curso1->setNombre("Matematica");
        $curso1->setNroImplementacion(123);
        $curso1->setNroOrdenanza(123);
        $curso1->setHoras(12);

        $manager->persist($curso1);

        // Alumnos
        $alumno1 = new Alumno();
        $alumno1->setNombre("Franco");
        $alumno1->setApellido("Cosolito");
        $alumno1->setDni(1234);
        $alumno1->setEmail("franco@mail.com");
        
        $alumno2 = new Alumno();
        $alumno2->setNombre("Enzo");
        $alumno2->setApellido("Garello");
        $alumno2->setDni(12345);
        $alumno2->setEmail("enzo@mail.com");

        $manager->persist($alumno1);
        $manager->persist($alumno2);

        // PerteneceA
        $curso1Carrera1 = new PerteneceA();
        $curso1Carrera1->setCurso($curso1);
        $curso1Carrera1->setCarrera($carrera1);
        $curso1Carrera1->setEsElectivo(false);

        $manager->persist($curso1Carrera1);

        // Ediciones
        $edicion1 = new Edicion();
        $edicion1->setCurso($curso1);
        $edicion1->setNombre("Matematica 2025");
        $edicion1->setFechaInicio(new DateTime());
        $edicion1->setFechaFin(new DateTime());
        $edicion1->setPrecio(5000.0);

        $manager->persist($edicion1);

        // Descuentos
        $descuento1 = new Descuento();
        $descuento1->setDescripcion("Descuento 1");
        $descuento1->setValor(0.5);

        $manager->persist($descuento1);

        // Inscripciones a Ediciones
        $insc1 = new InscripcionEdicion();
        $insc1->setAlumno($alumno1);
        $insc1->setEdicion($edicion1);
        $insc1->setDescuento($descuento1);

        $insc2 = new InscripcionEdicion();
        $insc2->setAlumno($alumno2);
        $insc2->setEdicion($edicion1);
        $insc2->setDescuento($descuento1);

        $manager->persist($insc1);
        $manager->persist($insc2);

        // Notas
        $nota1 = new Nota();
        $nota1->setValor(8.0);
        $nota1->setInscripcionEdicion($insc1);
        $nota1->setFechaCarga(new DateTime());
        $insc1->setNota($nota1);

        $manager->persist($nota1);

        // Persistir cambios
        $manager->flush();
    }
}
