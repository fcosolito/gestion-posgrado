import { useState } from 'react';

export default function ListaInscripciones ({ alumnos, descuentos }) {
    const [editingRow, setEditingRow] = useState();

    function handleEdit(row) {

    }
    return (
        <div className="rounded bg-white h-100 p-3">
                <div class="row p-2 d-flex justify-content-between">
                    <div class="col d-flex align-items-center justify-content-start">
                        <span class="m-1 fs-5 fw-bold">Alumnos</span>
                    </div>
                    <div class="col d-flex align-items-center justify-content-end">
                        <button class="btn btn-primary">Inscribir</button>
                    </div>
                </div>
                <div className="h-25 overflow-scroll">
                    <table className="table table-striped table-bordered table-hover">
                        <thead className="sticky-top table-secondary">
                            <tr>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>DNI</th>
                                <th>Descuento</th>
                                <th>Nota</th>
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
                                        <select className="form-select">
                                            <option selected>{alumno.descuento}</option>
                                        </select>
                                    </td>
                                    <td>{alumno.nota}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acciones
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item btn btn-secondary" href={`/alumno/${alumno.id}`}>
                                                        Ver
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>

                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
    );
}