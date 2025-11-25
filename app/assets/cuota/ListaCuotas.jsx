import { useState } from 'react';

export default function ListaCuotas({ cuotas, onSelect }) {
    const [idSeleccion, setIdSeleccion] = useState();

    function handleClick(cuota) {
        setIdSeleccion(cuota.id);
        onSelect(cuota);
    }

    return (
            <div className="rounded bg-white h-100 p-3 ps-0 pe-0">
                <h3 className="ms-3">Cuotas</h3>
                <div className="h-75 overflow-scroll">
                    <table className="table table-striped table-bordered table-hover">
                        <thead className="sticky-top table-secondary">
                            <tr>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Valor</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            {cuotas.map((cuota) => (
                                cuota.inscripcionEdicion ? (
                                    <tr
                                        key={cuota.id}
                                        onClick={() => handleClick(cuota)}
                                        className={`cursor-pointer ${
                                            idSeleccion === cuota.id ? "table-primary" : ""
                                        }`}
                                    >
                                        <td>{cuota.inscripcionEdicion.alumnoNombre}</td>
                                        <td>{cuota.inscripcionEdicion.alumnoApellido}</td>
                                        <td>{cuota.valor}</td>
                                        <td>{cuota.estado}</td>
                                    </tr>
                                ) : (
                                    <tr
                                        key={cuota.id}
                                        onClick={() => handleClick(cuota)}
                                        className={`cursor-pointer ${
                                            idSeleccion === cuota.id ? "table-primary" : ""
                                        }`}
                                    >
                                        <td>{cuota.inscripcionCarrera.alumnoNombre}</td>
                                        <td>{cuota.inscripcionCarrera.alumnoApellido}</td>
                                        <td>{cuota.valor}</td>
                                        <td>{cuota.estado}</td>
                                    </tr>
                                )

                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
    );
}