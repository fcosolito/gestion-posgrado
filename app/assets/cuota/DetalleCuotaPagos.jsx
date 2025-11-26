import { useState } from 'react';

import BuscadorDropdown from "../components/BuscadorDropdown";

export default function DetalleCuotaPagos({ cuota }){
    const [pago, setPago] = useState(null);
    const [montoCuota, setMontoCuota] = useState(null);

    async function fetchPagos(query) {
        return await fetch(`/pago/search?query=${query}`, {
                method: "GET",
                headers: { "Content-Type": "application/json" },
                });
    }

    const getDetallePago = (pago) => {
        return `$${pago.monto} (${pago.fechaPago})`;
    }

    const getLabelPago = (pago) => {
        return `${pago.id}`;
    }

    const asociarPago = async () => {
        try {
            const res = await fetch(`/pago/${pago.id}/asoc-cuota/${cuota.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ montoCuota: montoCuota}),
            });
            window.location.reload();
        } catch (err) {
            window.location.reload();
        }
    }

    const handleDesasociar = async (pago) => {
        try {
            const res = await fetch(`/pago/${pago.id}/desasoc-cuota/${cuota.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            });

            window.location.reload();
        } catch (err) {
            window.location.reload();
        }
    }

    const setPagoYMonto = (pago) => {
        setPago(pago);
        if (pago) {
            setMontoCuota(pago.item.monto);
        } else {
            setMontoCuota(null);
        }
    }

    const handleChange = (montoCuota) => {
        setMontoCuota(montoCuota);
    }

    if (!cuota) {
        return (
            <div className="container rounded bg-white p-3">
                <div className="row">
                    <div className="col-6">
                        <h3>Alumno</h3>
                    </div>
                </div>
                <hr></hr>
                <div className="row">
                    <div className="col">
                        <div>
                            <i>Seleccione una cuota para ver mas informacion</i>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="container rounded bg-white p-0 vh-100">
            <div className="p-3">
            <div className="row">
                <div className="col">
                    <h3>Alumno</h3>
                </div>
                <div className="col">
                </div>
            </div>
            {cuota.inscripcionEdicion ? (
                <>
                <div className="row">
                    <div className="col">
                        <div><strong>Nombre</strong></div>
                        <div>{cuota.inscripcionEdicion.alumnoNombre}</div>
                    </div>
                    <div className="col">
                        <div><strong>Apellido</strong></div>
                        <div>{cuota.inscripcionEdicion.alumnoApellido}</div>
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div><strong>DNI</strong></div>
                        <div>{cuota.inscripcionEdicion.alumnoDni}</div>
                    </div>
                    <div className="col">
                        <div><strong>Legajo</strong></div>
                        <div>{cuota.inscripcionEdicion.nroLegajo}</div>
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div><strong>Descuento</strong></div>
                        <div>{cuota.descuento ? cuota.descuento * 100 : 0}%</div>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <hr></hr>
                <div className="row">
                    <div className="col">
                        <h3>Edicion</h3>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div><strong>Nombre</strong></div>
                        <div>{cuota.inscripcionEdicion.edicionNombre}</div>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div><strong>Fecha de Inicio</strong></div>
                        <div>{cuota.inscripcionEdicion.edicionFechaInicio}</div>
                    </div>
                    <div className="col">
                        <div><strong>Fecha de Fin</strong></div>
                        <div>{cuota.inscripcionEdicion.edicionFechaFin ? cuota.inscripcionEdicion.edicionFechaFin : "-"}</div>
                    </div>
                </div>
                </>
            ) : (
                <>
                <div className="row">
                    <div className="col">
                        <div><strong>Nombre</strong></div>
                        <div>{cuota.inscripcionCarrera.alumnoNombre}</div>
                    </div>
                    <div className="col">
                        <div><strong>Apellido</strong></div>
                        <div>{cuota.inscripcionCarrera.alumnoApellido}</div>
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div><strong>DNI</strong></div>
                        <div>{cuota.inscripcionCarrera.alumnoDni}</div>
                    </div>
                    <div className="col">
                        <div><strong>Legajo</strong></div>
                        <div>{cuota.inscripcionCarrera.nroLegajo}</div>
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div><strong>Descuento</strong></div>
                        <div>{cuota.descuento ? cuota.descuento * 100 : 0}%</div>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <hr></hr>
                <div className="row">
                    <div className="col">
                        <h3>Carrera</h3>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div><strong>Nombre</strong></div>
                        <div>{cuota.inscripcionCarrera.carreraNombre}</div>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div><strong>Ordenanza</strong></div>
                        <div>{cuota.inscripcionCarrera.carreraNroOrdenanza}</div>
                    </div>
                    <div className="col">
                        <div><strong>Implementacion</strong></div>
                        <div>{cuota.inscripcionCarrera.carreraNroImplementacion}</div>
                    </div>
                </div>
                </>
            )}
            <hr></hr>
            </div>
            
            <div className="ps-0 pe-0 h-100">
            <div className="row">
                <div className="col">
                    <h3 className="ms-3 mb-2">Pagos</h3>
                </div>
                <div className="col text-end">
                    <button className="btn btn-primary mb-2 me-3"
                        onClick={() => {window.location.href="/pago/new";}}
                    >
                        Nuevo
                    </button>
                </div>
            </div>
            <div className="row pb-2 pt-2 ms-1 form-group">
                <div className="col">
                    <BuscadorDropdown 
                        fetchItems={fetchPagos}
                        placeholder={"Buscar pago..."}
                        setItem={setPagoYMonto}
                        getId={(pago) => pago.id}
                        getLabel={getLabelPago}
                        getDetalle={getDetallePago}
                    />
                </div>
                <div className="col">
                    <input
                        onChange={(e) => handleChange(e.target.value)}
                        type="number"
                        value={montoCuota ? montoCuota : ""}
                        className="form-control"
                        placeholder="Monto asociado"
                    />
                </div>
                <div className="col text-end me-3">
                    <button
                        onClick={asociarPago}
                        className="btn btn-secondary"
                    >
                        Asociar Pago
                    </button>
                </div>
            </div>
            <div className="h-100 overflow-scroll">
                <table className="table table-striped table-bordered table-hover">
                    <thead className="sticky-top table-secondary">
                        <tr>
                            <th>Id</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Comprobante</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {cuota.pagos.map((pago) => (
                            <tr
                                key={pago.id}
                            >
                                <td>{pago.id}</td>
                                <td>{pago.monto}</td>
                                <td>{pago.fechaPago}</td>
                                <td>{pago.comprobanteArchivo}</td>
                                <td>
                                    <div className="dropdown">
                                        <button className="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Acciones
                                        </button>
                                        <ul className="dropdown-menu" style={{ zIndex: 1050,}}>
                                            <li><a className="dropdown-item" href={`/pago/${pago.id}`}>Ver</a></li>
                                            <li><a className="dropdown-item" href={`/pago/${pago.id}/edit`}>Editar</a></li>
                                            <li>
                                                <button
                                                    onClick={() => handleDesasociar(pago)}
                                                    className="dropdown-item"
                                                >
                                                    Desasociar
                                                </button>
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
        </div>
    );
}
