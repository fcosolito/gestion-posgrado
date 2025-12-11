import { useState } from 'react';

import BuscadorDropdown from "../components/BuscadorDropdown";
import DropdownAcciones from '../components/DropdownAcciones';

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
            <div className="cuadrado-reutilizable bg-white p-3">
                <div className="row">
                    <div className="col-6">
                        <h4>Detalle de la cuota</h4>
                    </div>
                </div>
                <hr></hr>
                <div className="row">
                    <div className="col text-center">
                        <div>
                            <i>Seleccione una cuota para ver mas informacion</i>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="container cuadrado-reutilizable p-0 ">
            <div className="p-3">
            <div className="row">
                <div className="col">
                    <h4>Detalle de la cuota</h4>
                </div>
                <div className="col">
                </div>
            </div>
            <hr></hr>
            <div className="row">
                <div className="col">
                    <h5 className="pb-2 fw-bold">Alumno</h5>
                </div>
                <div className="col">
                </div>
            </div>
            {cuota.inscripcionEdicion ? (
                <>
                <div className="row mb-1">
                    <div className="col">
                        <div className="fw-normal">Nombre</div>
                        <div className="fw-light">{cuota.inscripcionEdicion.alumnoNombre}</div>
                    </div>
                    <div className="col">
                        <div className="fw-normal">Apellido</div>
                        <div className="fw-light">{cuota.inscripcionEdicion.alumnoApellido}</div>
                    </div>
                </div>
                <div className="row mb-1">
                    <div className="col">
                        <div className="fw-normal">DNI</div>
                        <div className="fw-light">{cuota.inscripcionEdicion.alumnoDni}</div>
                    </div>
                    <div className="col">
                        <div className="fw-normal">Legajo</div>
                        <div className="fw-light">{cuota.inscripcionEdicion.nroLegajo}</div>
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div className="fw-normal">Descuento</div>
                        <div className="fw-light">{cuota.descuento ?? 0}%</div>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <hr></hr>
                <div className="row">
                    <div className="col">
                        <h5 className="pb-2 fw-bold">Edicion</h5>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <div className="row mb-1">
                    <div className="col">
                        <div className="fw-normal">Nombre</div>
                        <div className="fw-light">{cuota.inscripcionEdicion.edicionNombre}</div>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div className="fw-normal">Fecha de inicio</div>
                        <div className="fw-light">{cuota.inscripcionEdicion.edicionFechaInicio}</div>
                    </div>
                    <div className="col">
                        <div className="fw-normal">Fecha de fin</div>
                        <div className="fw-light">{cuota.inscripcionEdicion.edicionFechaFin ? cuota.inscripcionEdicion.edicionFechaFin : "-"}</div>
                    </div>
                </div>
                </>
            ) : (
                <>
                <div className="row mb-1">
                    <div className="col">
                        <div className="fw-normal">Nombre</div>
                        <div className="fw-light">{cuota.inscripcionCarrera.alumnoNombre}</div>
                    </div>
                    <div className="col">
                        <div className="fw-normal">Apellido</div>
                        <div className="fw-light">{cuota.inscripcionCarrera.alumnoApellido}</div>
                    </div>
                </div>
                <div className="row mb-1">
                    <div className="col">
                        <div className="fw-normal">DNI</div>
                        <div className="fw-light">{cuota.inscripcionCarrera.alumnoDni}</div>
                    </div>
                    <div className="col">
                        <div className="fw-normal">Legajo</div>
                        <div className="fw-light">{cuota.inscripcionCarrera.nroLegajo}</div>
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div className="fw-normal">Descuento</div>
                        <div className="fw-light">{cuota.descuento ?? 0}%</div>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <hr></hr>
                <div className="row mb-1">
                    <div className="col">
                        <h5 className="pb-2 fw-bold">Carrera</h5>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <div className="row mb-1">
                    <div className="col">
                        <div className="fw-normal">Nombre</div>
                        <div className="fw-light">{cuota.inscripcionCarrera.carreraNombre}</div>
                    </div>
                    <div className="col">
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <div className="fw-normal">Ordenanza</div>
                        <div className="fw-light">{cuota.inscripcionCarrera.carreraNroOrdenanza}</div>
                    </div>
                    <div className="col">
                        <div className="fw-normal">Implementación</div>
                        <div className="fw-light">{cuota.inscripcionCarrera.carreraNroImplementacion}</div>
                    </div>
                </div>
                </>
            )}
            <hr></hr>
            </div>
            
            <div className="ps-0 pe-0 h-100">
            <div className="row">
                <div className="col-auto">
                    <h5 className="ms-3 mb-2 fw-bold">Asociar pago a esta cuota</h5>
                </div>
            </div>
            <div className="row pb-2 pt-2 ms-1 form-group align-items-end">
                <div className="col">
                    <span className="ms-1 fw-normal">Pago</span>
                    <br className="m-2"></br>
                    <BuscadorDropdown 
                        fetchItems={fetchPagos}
                        placeholder={"Descripcion/ID"}
                        setItem={setPagoYMonto}
                        getId={(pago) => pago.id}
                        getLabel={getLabelPago}
                        getDetalle={getDetallePago}
                    />
                </div>
                <div className="col">
                    <span className="ms-1 fw-normal">Monto asociado</span>
                    <br className="m-2"></br>
                    <input
                        onChange={(e) => handleChange(e.target.value)}
                        type="number"
                        value={montoCuota ? montoCuota : ""}
                        className="form-control"
                        placeholder="Monto cuota"
                    />
                </div>
                <div className="col text-end me-3">
                    <button
                        onClick={asociarPago}
                        className="btn-verde"
                    >
                        Asociar Pago
                    </button>
                </div>
            </div>
            <hr className="m-3"></hr>
            <div className="row">
                <div className="col-auto">
                    <h5 className="ms-3 mb-2 fw-bold">Pagos</h5>
                </div>
                <div className="col text-end">
                    <button className="btn-verde mb-2 me-3"
                        onClick={() => {window.location.href="/pago/new";}}
                    >
                        Nuevo
                    </button>
                </div>
            </div>
            <div className="h-100 overflow-scroll">
                <table className="table table-bordered">
                    <thead className="sticky-top table-secondary">
                        <tr>
                            <th className="fw-normal">Id</th>
                            <th className="fw-normal">Fecha</th>
                            <th className="fw-normal">Monto</th>
                            <th className="fw-normal">Monto asociado</th>
                            <th className="fw-normal">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        {cuota.pagos.map((pago) => (
                            <tr
                                key={pago.id}
                            >
                                <td>{pago.id}</td>
                                <td>{pago.fechaPago}</td>
                                <td>{pago.monto}</td>
                                <td>{pago.montoAsociado}</td>
                                <td>
                                    <DropdownAcciones
                                        rowId={pago.id}
                                        opciones={[
                                            {
                                                label: 'Ver',
                                                onClick: (id) => window.location.href = `/pago/${id}`
                                            },
                                            {
                                                label: 'Editar',
                                                onClick: (id) => window.location.href = `/pago/${id}/edit`
                                            },
                                            {
                                                label: 'Desasociar',
                                                onClick: (id) => handleDesasociar(pago)
                                            }
                                        ]}
                                    />
                                </td>
                            </tr>
                        ))}
                            <tr><td colSpan="5" className="text-center">Monto total asociado a la cuota: ${cuota.montoTotalAsociado}</td></tr>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    );
}
