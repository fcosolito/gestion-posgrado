import { useState } from 'react';

export default function ListaCuotas({ cuotas, onSelect }) {
    const [idSeleccion, setIdSeleccion] = useState();

    function handleClick(cuota) {
        setIdSeleccion(cuota.id);
        onSelect(cuota);
    }

    return (
            <div className="cuadrado-reutilizable p-3 ps-0 pe-0">
                <h4 className="ms-3">Cuotas</h4>
                <div className="overflow-scroll" style={{maxHeight: "70vh",}}>
                    <table className="table table-bordered table-hover">
                        <thead className="sticky-top table-secondary">
                            <tr>
                                <th className="fw-normal">Nombre</th>
                                <th className="fw-normal">Apellido</th>
                                <th className="fw-normal">Valor</th>
                                <th className="fw-normal">Estado</th>
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
                                        <td className="fw-light">{cuota.inscripcionEdicion.alumnoNombre}</td>
                                        <td className="fw-light">{cuota.inscripcionEdicion.alumnoApellido}</td>
                                        <td className="fw-light">{cuota.valor}</td>
                                        <td className="fw-light">{cuota.estado}</td>
                                    </tr>
                                ) : (
                                    <tr
                                        key={cuota.id}
                                        onClick={() => handleClick(cuota)}
                                        className={`cursor-pointer ${
                                            idSeleccion === cuota.id ? "table-primary" : ""
                                        }`}
                                    >
                                        <td className="fw-light">{cuota.inscripcionCarrera.alumnoNombre}</td>
                                        <td className="fw-light">{cuota.inscripcionCarrera.alumnoApellido}</td>
                                        <td className="fw-light">{cuota.valor}</td>
                                        <td className="fw-light">{cuota.estado}</td>
                                    </tr>
                                )
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
    );
}