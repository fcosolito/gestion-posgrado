import { useState } from 'react';

export default function ListaAlumnos ({ carrera, alumnos, descuentos}) {
    const [editValues, setEditValues] = useState({});
    const [editingRow, setEditingRow] = useState(null);

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

    const handleSave = async (inscripcion) => {
        try {
            const res = await fetch(`/carrera/${carrera.id}/edit-insc/${inscripcion}`, {
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
    
    const handleDesinscribir = async (alumno) => {
        try {
            const res = await fetch(`/carrera/${carrera.id}/desinsc-alumno/${alumno}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            });
        } catch (err) {
        }
        window.location.reload();
    };
    return (
        <div className="alumnos-inscriptos">
            <div className="header">
                <h2>Alumnos Inscriptos ({ alumnos.length })</h2>
            </div>
            <div className="body-alumnos">
                <div className="tabla-alumnos">
                    <table>
                        <thead>
                            <tr>
                                <th>Apellido y Nombre</th>
                                <th>Legajo</th>
                                <th>Descuento</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            {alumnos.length > 0 ? (
                                alumnos.map((alumno) => (
                                    editingRow === alumno.inscripcion ? (
                                        <tr key={alumno.inscripcion}>
                                            <td>{ alumno.apellido }, { alumno.nombre }</td>
                                            <td>
                                                <input
                                                    value={editValues.nroLegajo ? editValues.nroLegajo : ""}
                                                    onChange={(e) => handleChange("nroLegajo", e.target.value)}
                                                    type="number"
                                                    className="form-control"
                                                />
                                            </td>
                                            <td>
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
                                            </td>
                                            <td className="container">
                                                <div className="row">
                                                    <div className="col">
                                                        <button
                                                            onClick={() => handleSave(alumno.inscripcion)}
                                                            className="btn btn-primary"
                                                        >Guardar</button>
                                                    </div>
                                                    <div className="col">
                                                        <button
                                                            onClick={handleCancel}
                                                            className="btn btn-danger"
                                                        >Cancelar</button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    ) : (
                                        <tr key={alumno.inscripcion}>
                                            <td>{ alumno.apellido }, { alumno.nombre }</td>
                                            <td>{ alumno.nroLegajo ? alumno.nroLegajo : ""}</td>
                                            <td>{ alumno.descuento ? descuentos.filter(d => d.id === alumno.descuento)[0].valor : 0 }</td>
                                            <td>
                                                <div className="dropdown">
                                                    <button className="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Acciones
                                                    </button>
                                                    <ul className="dropdown-menu">
                                                        <li><a className="dropdown-item" href={`/alumno/${alumno.id}/visualizar`}>Ver</a></li>
                                                        <li>
                                                            <button
                                                                onClick={() => handleEdit(alumno)}
                                                                className="dropdown-item"
                                                            >Editar</button>
                                                        </li>
                                                        <li>
                                                            <button
                                                                onClick={() => handleDesinscribir(alumno.id)}
                                                                className="dropdown-item"
                                                            >Eliminar inscripcion</button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    )
                                ))
                            ) : (
                                <tr key="sin inscriptos">
                                    <td colSpan="6" className="text-center text-muted">No hay alumnos inscriptos en esta carrera</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}