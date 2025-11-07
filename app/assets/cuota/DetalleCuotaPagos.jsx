export default function DetalleCuotaPagos({ cuota }){
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
        <div className="container rounded bg-white p-3">
            <div className="row">
                <div className="col">
                    <h3>Alumno</h3>
                </div>
                <div className="col">
                    <button className="btn btn-secondary">Ver inscripcion</button>
                </div>
            </div>
            <hr></hr>
            <div className="row">
                <div className="col">
                    <div><strong>Nombre</strong></div>
                    <div>{cuota.nombreAlumno}</div>
                </div>
                <div className="col">
                    <div><strong>Apellido</strong></div>
                    <div>{cuota.apellidoAlumno}</div>
                </div>
            </div>
            <div className="row">
                <div className="col">
                    <div><strong>DNI</strong></div>
                    <div>{cuota.dniAlumno}</div>
                </div>
                <div className="col">
                    <div><strong>Descuento</strong></div>
                    <div>{cuota.descuento * 100}%</div>
                </div>
            </div>
            <hr></hr>
            <div className="row">
                <div className="col">
                    <h3>Pagos</h3>
                </div>
                <div className="col">
                    <button className="btn btn-primary"
                        onClick={() => {window.location.href="/pago/new";}}
                    >
                        Nuevo
                    </button>
                </div>
            </div>
            <div className="h-25 overflow-scroll">
                <table className="table table-striped table-bordered table-hover">
                    <thead className="sticky-top table-secondary">
                        <tr>
                            <th className="bg-grey">Id</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        {cuota.pagos.map((pago) => (
                            <tr
                                key={pago.id}
                            >
                                <td>{pago.id}</td>
                                <td>{pago.monto}</td>
                                <td>{pago.fechaDePago.toString()}</td>
                                <td>{pago.comprobante}</td>
                            </tr>

                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
