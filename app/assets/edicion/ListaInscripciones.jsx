import { useState } from 'react';

import BuscadorDropdown from "../components/BuscadorDropdown";

export default function ListaInscripciones ({ edicion, alumnos, descuentos }) {
    const [editingRow, setEditingRow] = useState(null);
    const [editValues, setEditValues] = useState([]);
    const [inscripcion, setInscripcion] = useState(null);
    // no guarda datos relevantes, setearlo a null limpia el buscador de alumno
    const [limpiarAlumno, setLimpiarAlumno] = useState(null);

    function handleEdit(alumno) {
        setEditingRow(alumno.inscripcion);
        setEditValues(alumno);
    };

    function handleCancel(){
        setEditingRow(null);
        setEditValues({});
    };

    const handleChange = (field, value) => {
        setEditValues((prev) => ({ ...prev, [field]: value }));
    };

    const handleSave = async (id) => {
        try {
            const res = await fetch(`/edicion/${edicion.id}/edit-insc/${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(editValues),
            });
        } catch (err) {
        }
        setEditingRow(null);
        setEditValues({});
        window.location.reload();
    };

    const handleInscribir = async () => {
        try {
            const res = await fetch(`/edicion/${edicion.id}/insc-alumno/${inscripcion.alumno}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(inscripcion),
            });
        } catch (err) {
        }
        setLimpiarAlumno(null);
        setInscripcion(null)
        window.location.reload();
    };

    const handleInscripcionChange = (field, value) => {
        setInscripcion((prev) => ({ ...prev, [field]: value}));
    };

    async function fetchAlumnos(query) {
        return await fetch(`/alumno/search?query=${query}`, {
                method: "GET",
                headers: { "Content-Type": "application/json" },
                });
    }

    function getDetalleAlumno(alumno) {
        return `DNI ${alumno.dni}`;
    }

    return (
        <div className="cuadrado-reutilizable fill-remaining vh-75">
                <div className="row p-3 d-flex justify-content-between align-items-start">
                    <div className="col d-flex align-items-center justify-content-start">
                        <h4>Alumnos | Inscribir alumno</h4>
                    </div>
                    <div className="col d-flex align-items-center justify-content-end">
                        <div className="vr me-3"></div>
                        <div className="row form-group align-items-end">
                            <div className="col">
                                <div className="form-label ps-1">Alumno</div>
                                <BuscadorDropdown
                                    fetchItems={fetchAlumnos}
                                    item={limpiarAlumno}
                                    setItem={(alumno) => handleInscripcionChange("alumno", alumno ? alumno.id : null)}
                                    placeholder={"Buscar alumno..."}
                                    getId={(alumno) => alumno.id}
                                    getLabel={(alumno) => `${alumno.nombre} ${alumno.apellido}`}
                                    getDetalle={getDetalleAlumno}
                                />
                            </div>
                            <div className="col">
                                <div className="form-label ps-1">Legajo</div>
                                <input
                                    type="number"
                                    className="form-control"
                                    placeholder="Numero de legajo..."
                                    onChange={(e) => handleInscripcionChange("nroLegajo", e.target.value)}
                                />
                            </div>
                            <div className="col">
                                <div className="form-label ps-1">Descuento</div>
                                <div className="dropdown">
                                    <button className="btn btn-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {(inscripcion && inscripcion.descuento) ? descuentos.filter(d => d.id === inscripcion.descuento)[0].valor : "Descuento..."}
                                    </button>
                                    <ul className="dropdown-menu w-100" style={{ zIndex: 1050,}}>
                                        {descuentos.map((descuento) => (
                                            <li key={descuento.id}>
                                                <button
                                                    className="dropdown-item"
                                                    onClick={() => handleInscripcionChange("descuento", descuento.id)}
                                                >
                                                    <div><strong>{descuento.valor}</strong></div>
                                                    <div className="text-truncate">{`${descuento.descripcion}`}</div>
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                            <div className="col">
                                <div className="form-label ps-1">Fecha de Inscripcion</div>
                                <input
                                    type="date"
                                    className="form-control"
                                    placeholder="Numero de legajo..."
                                    onChange={(e) => handleInscripcionChange("fechaInscripcion", e.target.value)}
                                />
                            </div>
                            <div className="col">
                                <div className="flex-grow"></div>
                                <button 
                                    className="btn-verde"
                                    onClick={handleInscribir}
                                >
                                    Inscribir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="h-100 overflow-scroll">
                    <table className="table table-bordered ">
                        <thead className="sticky-top table-secondary">
                            <tr>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>DNI</th>
                                <th>Legajo</th>
                                <th>Descuento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {alumnos.map((alumno) => (
                                <tr
                                    key={alumno.inscripcion}
                                >
                                    <td>{alumno.nombre}</td>
                                    <td>{alumno.apellido}</td>
                                    <td>{alumno.dni}</td>
                                    <td>
                                        {editingRow === alumno.inscripcion ? (
                                            <input
                                                value={editValues.nroLegajo ? editValues.nroLegajo : ""}
                                                onChange={(e) => handleChange("nroLegajo", e.target.value)}
                                                type="number"
                                                className="form-control"
                                            />
                                        ) : (
                                            alumno.nroLegajo ? alumno.nroLegajo : ""
                                        )}
                                    </td>
                                    <td>
                                    {editingRow === alumno.inscripcion ? (
                                        <div className="dropdown">
                                            <button className="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {editValues.descuento ? descuentos.filter(d => d.id === editValues.descuento)[0].valor : 0}
                                            </button>
                                            <ul className="dropdown-menu w-100" style={{ zIndex: 1050,}}>
                                                {descuentos.map((descuento) => (
                                                    <li key={descuento.id}>
                                                        <button
                                                            className="dropdown-item"
                                                            onClick={() => handleChange("descuento", descuento.id)}
                                                        >
                                                            <div><strong>{descuento.valor}</strong></div>
                                                            <div className="text-truncate">{`${descuento.descripcion}`}</div>
                                                        </button>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    ) : (
                                        // TODO manejar errores
                                        alumno.descuento ? descuentos.filter(d => d.id === alumno.descuento)[0].valor : 0
                                    )}
                                    </td>
                                    <td>
                                        {editingRow === alumno.inscripcion ? (
                                            <div className="row">
                                                <div className="col-auto">
                                                    <button
                                                        onClick={() => handleSave(alumno.inscripcion)}
                                                        className="btn btn-primary"
                                                    >
                                                        Guardar
                                                    </button>
                                                </div>
                                                <div className="col-auto">
                                                    <button
                                                        onClick={handleCancel}
                                                        className="btn btn-danger"
                                                    >
                                                        Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="dropdown">
                                                <button className="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Acciones
                                                </button>
                                                <ul className="dropdown-menu">
                                                    <li>
                                                        <a className="dropdown-item btn btn-secondary" href={`/alumno/${alumno.id}/visualizar`}>
                                                            Ver
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button
                                                            className="dropdown-item btn btn-secondary"
                                                            onClick={() => handleEdit(alumno)}
                                                        >
                                                            Editar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        )}

                                    </td>
                                </tr>

                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
    );
}