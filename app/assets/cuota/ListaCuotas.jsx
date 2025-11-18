import { useState } from 'react';

export default function ListaCuotas({ cuotas, onSelect }) {
    const [idSeleccion, setIdSeleccion] = useState();

    function handleClick(cuota) {
        setIdSeleccion(cuota.id);
        onSelect(cuota);
    }

    return (
            <div className="rounded bg-white h-100 p-3">
                <h3>Cuotas</h3>
                <div className="h-25 overflow-scroll">
                    <table className="table table-striped table-bordered table-hover">
                        <thead className="sticky-top table-secondary">
                            <tr>
                                <th className="bg-grey">Nombre</th>
                                <th>Apellido</th>
                                <th>Valor</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {cuotas.map((cuota) => (
                                <tr
                                    key={cuota.id}
                                    onClick={() => handleClick(cuota)}
                                    className={`cursor-pointer ${
                                        idSeleccion === cuota.id ? "table-primary" : ""
                                    }`}
                                >
                                    <td>{cuota.nombreAlumno}</td>
                                    <td>{cuota.apellidoAlumno}</td>
                                    <td>{cuota.valor}</td>
                                    <td>{cuota.estado}</td>
                                    <td><button className="btn btn-secondary">Acciones</button></td>
                                </tr>

                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
    );
}