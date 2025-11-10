import { useState } from 'react';

export default function ListaInscripciones ({ alumnos, descuentos }) {
    const [editingRow, setEditingRow] = useState(null);
    const [editValues, setEditValues] = useState([]);

    function handleEdit(alumno) {
        setEditingRow(alumno.inscripcion);
        setEditValues(alumno);
    }

    function handleCancel(){
        setEditingRow(null);
        setEditValues({});
    }

    const handleChange = (field, value) => {
        setEditValues((prev) => ({ ...prev, [field]: value }));
        console.log(`Edit values: ${editValues}`);
    };

    return (
        <div className="rounded bg-white h-100 p-3">
                <div className="row p-2 d-flex justify-content-between">
                    <div className="col d-flex align-items-center justify-content-start">
                        <span className="m-1 fs-5 fw-bold">Alumnos</span>
                    </div>
                    <div className="col d-flex align-items-center justify-content-end">
                        <button className="btn btn-primary">Inscribir</button>
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
                                    {editingRow === alumno.inscripcion ? (
                                        <select 
                                            className="form-select"
                                            onChange={(e) => handleChange("descuento", e.target.value)}
                                        >
                                            {descuentos.map((descuento) => (
                                                <option 
                                                    selected={alumno.descuento === descuento.id}
                                                    value={descuento.id}
                                                >
                                                    <div><strong>{descuento.valor}</strong></div>
                                                    <div>{`${descuento.descripcion.slice(0, 20)}...`}</div>
                                                </option>

                                            ))}
                                        </select>
                                    ) : (
                                        // Esto tiene pinta de poder fallar muy facil
                                        // TODO manejar errores
                                        descuentos.filter(d => d.id === alumno.descuento)[0].valor
                                    )}
                                    </td>
                                    <td>{alumno.nota}</td>
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
                                                        <a className="dropdown-item btn btn-secondary" href={`/alumno/${alumno.id}`}>
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