import { useState, useEffect, useRef } from 'react';

import BuscadorDropdown from "../components/BuscadorDropdown";
import SelectorDropdown from "../components/SelectorDropdown";

export default function FormBuscarCuotas ({carreraIni, cursoIni, edicionIni, alumnoIni}) {
    const [carrera, setCarrera] = useState(carreraIni);
    const [curso, setCurso] = useState(cursoIni);
    const [ediciones, setEdiciones] = useState([]);
    const [edicion, setEdicion] = useState(edicionIni)
    const [alumno, setAlumno] = useState(alumnoIni);
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return
        }

        if (!curso) {
            setEdiciones([]);
            return
        }

        const fetchData = async () => {
            try {
                const res = await fetch(`/edicion/${curso.id}/find-by-curso`, {
                    method: "GET",
                    headers: { "Content-Type": "application/json" },
                    });
                const data = await res.json();
                
                setEdiciones(data);
            } catch (err) {
                alert(err.message);
            }
        }

        fetchData();
    }, [curso])

    const handleSubmit = () => {
        const carreraParam = carrera ? `carrera=${carrera.id}&` : "";
        const cursoParam = curso ? `curso=${curso.id}&` : "";
        const edicionParam = edicion ? `edicion=${edicion.id}&` : "";
        const alumnoParam = alumno ? `alumno=${alumno.id}&` : "";
        window.location.href = `${window.location.pathname}?${carreraParam}${cursoParam}${edicionParam}${alumnoParam}`;
    }

    async function fetchCarreras(query) {
        return await fetch(`/carrera/search?query=${query}`, {
                method: "GET",
                headers: { "Content-Type": "application/json" },
                });
    }

    async function fetchCursos(query) {
        return await fetch(`/curso/search?query=${query}`, {
                method: "GET",
                headers: { "Content-Type": "application/json" },
                });
    }

    async function fetchAlumnos(query) {
        return await fetch(`/alumno/search?query=${query}`, {
                method: "GET",
                headers: { "Content-Type": "application/json" },
                });
    }

    function getDetalleCarrera(carrera) {
        return `Ord. ${carrera.nroOrdenanza}, Impl. ${carrera.nroImplementacion}`;
    }

    function getDetalleCurso(curso) {
        return `Ord. ${curso.nroOrdenanza}, Impl. ${curso.nroImplementacion}`;
    }

    function getDetalleEdicion(edicion) {
        return `Ini. ${edicion.fechaInicio}`;
    }

    function getDetalleAlumno(alumno) {
        return `DNI ${alumno.dni}`;
    }

    return (
        <div className="row">
            <div className="col-2">
                <div className="p-1">
                    <BuscadorDropdown 
                        fetchItems={fetchCarreras}
                        placeholder={"Seleccionar carrera..."}
                        item={carrera}
                        setItem={setCarrera}
                        getId={(carrera) => carrera.id}
                        getLabel={(carrera) => carrera.nombre}
                        getDetalle={getDetalleCarrera}
                    />
                </div>
            </div>
            <div className="col-2">
                <div className="p-1">
                    <BuscadorDropdown
                        fetchItems={fetchCursos}
                        placeholder={"Seleccionar curso..."}
                        item={curso}
                        setItem={setCurso}
                        getId={(curso) => curso.id}
                        getLabel={(curso) => curso.nombre}
                        getDetalle={getDetalleCurso}
                    />
                </div>
            </div>
            <div className="col-2">
                <div className="p-1">
                    <SelectorDropdown
                        items={ediciones}
                        placeholder={"Seleccionar edicion..."}
                        item={edicion}
                        setItem={setEdicion}
                        getId={(edicion) => edicion.id}
                        getLabel={(edicion) => edicion.nombre}
                        getDetalle={getDetalleEdicion}
                    />
                </div>
            </div>
            <div className="col-2">
                <div className="p-1">
                    <BuscadorDropdown
                        fetchItems={fetchAlumnos}
                        placeholder={"Seleccionar alumno..."}
                        item={alumno}
                        setItem={setAlumno}
                        getId={(alumno) => alumno.id}
                        getLabel={(alumno) => `${alumno.nombre} ${alumno.apellido}`}
                        getDetalle={getDetalleAlumno}
                    />
                </div>
            </div>
            <div className="col-2">
                <div className="p-1">
                    <button 
                        className="btn btn-primary"
                        onClick={handleSubmit}
                    >
                        Filtrar
                    </button>
                </div>
            </div>
        </div>
    );
}