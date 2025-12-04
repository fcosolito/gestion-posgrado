<?php 

namespace App\Service;

use App\Entity\Alumno;
use App\Entity\Carrera;
use App\Entity\Cuota;
use App\Entity\Curso;
use App\Entity\PerteneceA;
use App\Entity\Descuento;
use App\Entity\InscripcionCarrera;
use App\Repository\CarreraRepository;
use App\Repository\InscripcionCarreraRepository;
use App\Repository\PerteneceARepository;
use App\Repository\PrecioCarreraRepository;
use App\Repository\DescuentoRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class CarreraService
{
    public function __construct(
        private CarreraRepository $carreraRepository,
        private InscripcionCarreraRepository $inscripcionCarreraRepository,
        private PerteneceARepository $perteneceARepository,
        private PrecioCarreraRepository $precioCarreraRepository,
        private DescuentoRepository $descuentoRepository,
        private EntityManagerInterface $em,
    ) {}

    // este método se usa en el método index del CarreraController
    public function getIndexData(array $criteria): array
    {
        try {
            $carreras = $this->carreraRepository->search($criteria);

            if ($carreras === null) {
                throw new \Exception("No se pudieron obtener las carreras.");
            }

            $inscriptosPorCarrera = [];

            foreach ($carreras as $carrera) {
                $inscriptos = $this->inscripcionCarreraRepository
                    ->findByCarrera($carrera->getId());

                $inscriptosPorCarrera[$carrera->getId()] = count($inscriptos);
            }

            return [
                'carreras' => $carreras,
                'inscriptosPorCarrera' => $inscriptosPorCarrera,
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error al cargar el listado de carreras: " . $e->getMessage());
        }
    }

    // Este método se usa en el método show de carreracontroller
    public function buildShowData(Carrera $carrera): array
    {
        try {

            //CURSOS
            $cursosRelacionados = $this->perteneceARepository->findBy(['carrera' => $carrera]);

            $cursosObligatorios = [];
            $cursosElectivos = [];

            foreach ($cursosRelacionados as $relacion) {
                if ($relacion->isEsElectivo()) {
                    $cursosElectivos[] = $relacion->getCurso();
                } else {
                    $cursosObligatorios[] = $relacion->getCurso();
                }
            }

            //INSCRIPCIONES
            $inscripciones = $this->inscripcionCarreraRepository
                ->findByCarrera($carrera->getId());

            $alumnos = array_map(function($inscripcion) {
                $alumno = $inscripcion->getAlumno();

                return [
                    "id" => $alumno->getId(),
                    "nombre" => $alumno->getNombre(),
                    "apellido" => $alumno->getApellido(),
                    "dni" => $alumno->getDni(),
                    "email" => $alumno->getEmail(),
                    "inscripcion" => $inscripcion->getId(),
                    "nroLegajo" => $inscripcion->getNroLegajo(),
                    "fechaInscripcion" => $inscripcion->getFechaInscripcion()
                        ? $inscripcion->getFechaInscripcion()->format("Y-m-d")
                        : null,
                    "descuento" => $inscripcion->getDescuento()
                        ? $inscripcion->getDescuento()->getId()
                        : null,
                ];
            }, $inscripciones);

            //DESCUENTOS
            $descuentos = array_map(function (Descuento $d) {
                return [
                    "id" => $d->getId(),
                    "valor" => $d->getValor(),
                    "descripcion" => $d->getDescripcion(),
                ];
            }, $this->descuentoRepository->findAll());

            //PRECIO VIGENTE
            $precioVigente = $this->precioCarreraRepository
                ->findPrecioVigentePorCarrera($carrera->getId());

            return [
                'success' => true,
                'data' => [
                    'carrera' => $carrera,
                    'carrera_ser' => [
                        "id" => $carrera->getId(),
                    ],
                    'inscriptosCarrera' => count($inscripciones),
                    'cursosObligatorios' => $cursosObligatorios,
                    'cursosElectivos' => $cursosElectivos,
                    'alumnos' => $alumnos,
                    'descuentos' => $descuentos,
                    'precioVigente' => $precioVigente,
                ]
            ];

        } catch (\Throwable $e) {

            return [
                'success' => false,
                'error' => 'Ocurrió un error al cargar la información de la carrera: ' . $e->getMessage()
            ];
        }
    }
    // Método que se usa en el asignarCurso de CarreraController.
    public function asignarCursoExistente(Carrera $carrera, int $cursoId, string $tipo): void
    {
        $curso = $this->em->getRepository(Curso::class)->find($cursoId);

        if (!$curso) {
            throw new \Exception("El curso seleccionado no existe.");
        }

        // Evitamos que se asigne un curso que ya lo tenemos asignado a la carrera.
        $yaExiste = $this->perteneceARepository->findOneBy([
            'carrera' => $carrera,
            'curso' => $curso
        ]);

        if ($yaExiste) {
            throw new \Exception("El curso ya está asociado a esta carrera.");
        }

        $pertenece = new PerteneceA();
        $pertenece->setCarrera($carrera);
        $pertenece->setCurso($curso);
        $pertenece->setEsElectivo($tipo === 'electivo');

        try {
            $this->em->persist($pertenece);
            $this->em->flush();
        } catch (\Throwable $e) {
            throw new \Exception("Ocurrió un error al asociar el curso. Intenta nuevamente.");
        }
    }

    public function crearYAsignarCurso(Carrera $carrera, array $data): void
    {
        // Validaciones básicas
        if (empty($data['nombre'])) {
            throw new \Exception("Debe ingresar un nombre para el curso.");
        }

        if (empty($data['cantidadHoras']) || !is_numeric($data['cantidadHoras'])) {
            throw new \Exception("La cantidad de horas es inválida.");
        }

        $tipo = $data['tipo_asignacion'] ?? null;
        if (!in_array($tipo, ['obligatorio', 'electivo'])) {
            throw new \Exception("El tipo de asignación es inválido.");
        }

        // Crear curso
        $curso = new Curso();
        $curso->setNombre($data['nombre']);
        $curso->setNroOrdenanza($data['nroOrdenanza'] ?? null);
        $curso->setNroImplementacion($data['nroImplementacion'] ?? null);
        $curso->setHoras((int) $data['cantidadHoras']);

        $this->em->persist($curso);

        // Crear relación PerteneceA
        $pertenece = new PerteneceA();
        $pertenece->setCarrera($carrera);
        $pertenece->setCurso($curso);
        $pertenece->setEsElectivo($tipo === 'electivo');

        $this->em->persist($pertenece);

        try {
            $this->em->flush();
        } catch (\Throwable $e) {
            throw new \Exception("Error al crear y asociar el curso. Intente nuevamente.");
        }
    }
    
    public function editarInscripcion(InscripcionCarrera $inscripcion, array $data): void
    {
        try {
            $descuento = isset($data['descuento']) && $data['descuento'] 
                ? $this->descuentoRepository->find($data['descuento']) 
                : null;
            
            $fechaInscripcion = isset($data['fechaInscripcion']) 
                ? DateTime::createFromFormat("Y-m-d", $data['fechaInscripcion']) 
                : new DateTime();
            
            $nroLegajo = isset($data['nroLegajo']) ? (int) $data['nroLegajo'] : null;
            
            $inscripcion->setDescuento($descuento);
            $inscripcion->setFechaInscripcion($fechaInscripcion);
                
            if ($nroLegajo) {
                // Verificar si el legajo ya existe (excepto para esta inscripción)
                $existing = $this->inscripcionCarreraRepository->findOneBy([
                    "carrera" => $inscripcion->getCarrera(), 
                    "nroLegajo" => $nroLegajo
                ]);
                
                if ($existing && $existing->getId() !== $inscripcion->getId()) {
                    throw new \DomainException("El legajo ya existe en la carrera.");
                }
                
                $inscripcion->setNroLegajo($nroLegajo);
            }
                
            $this->em->persist($inscripcion);
            $this->em->flush();
        } catch (\DomainException $e) {
            throw $e; // Re-lanzamos excepciones de dominio
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error al editar la inscripción: " . $e->getMessage());
        }
    }

    public function desinscribirAlumno(Carrera $carrera, Alumno $alumno): void
    {
        try {
            $inscripcion = $this->inscripcionCarreraRepository->findOneBy([
                "carrera" => $carrera, 
                "alumno" => $alumno
            ]);

            if (!$inscripcion) {
                throw new \DomainException("El alumno no está inscrito en esta carrera.");
            }

            // Eliminar cuotas asociadas
            $cuotaRepository = $this->em->getRepository(Cuota::class);
            $cuotas = $cuotaRepository->findBy(["inscripcionCarrera" => $inscripcion]);
            
            foreach ($cuotas as $cuota) {
                $this->em->remove($cuota);
            }

            $this->em->remove($inscripcion);
            $this->em->flush();
        } catch (\DomainException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error al desinscribir al alumno: " . $e->getMessage());
        }
    }

}
