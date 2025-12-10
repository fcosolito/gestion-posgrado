import {useState} from "react";
import DropdownAcciones from '../components/DropdownAcciones';

export default function Carreras ({ asociadas, curso }){
    const [isSearching, setIsSearching] = useState(false);
    const [carreras, setCarreras] = useState(asociadas);

    function handleSearch() {
        setIsSearching(true);
    }

    function handleCancel() {
        setCarreras(asociadas)
        setIsSearching(false);
    }

    async function handleChange(query) {
            try {
                const res = await fetch(`/carrera/search?query=${query}`, {
                method: "GET",
                headers: { "Content-Type": "application/json" },
                });

                const data = await res.json();
                setCarreras(data);
            } catch (err) {
            }
    }

    async function asociarElectivo(carrera) {
            try {
                const res = await fetch(`/carrera/${carrera.id}/asociar-curso/${curso.id}?electivo=1`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                });
            } catch (err) {
            }
            window.location.reload();
    }

    async function asociarObligatorio(carrera) {
            try {
                const res = await fetch(`/carrera/${carrera.id}/asociar-curso/${curso.id}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                });
            } catch (err) {
            }
            window.location.reload();
    }

    async function desasociar(carrera) {
            try {
                const res = await fetch(`/carrera/${carrera.id}/asociar-curso/${curso.id}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                });
            } catch (err) {
            }
            window.location.reload();
    }

    return (
        <div className="container cuadrado-reutilizable fill-remaining">
            <div className="row mb-1 p-3">
                <div className="col">
                    <h4>Carreras</h4>
                </div>
                <div className="col text-end">
                    {isSearching ? (
                        <button
                            className="btn-rojo"
                            onClick={() => handleCancel()}
                        >Cancelar</button>

                    ) : (
                         <button
                            className="btn-verde"
                            onClick={() => handleSearch()}
                        >Asociar carrera</button>

                    )}
                </div>
            </div>
            <div className="row">
                <div className="col">
                    {isSearching ? (
                        <>
                        <span className="ms-3 fw-normal">Filtro de carreras</span>
                        <br></br>
                        <input
                            type="text"
                            className="ms-3 mb-3 form-control"
                            placeholder="Buscar carreras..."
                            onChange={(e) => handleChange(e.target.value)}
                        />
                        </>
                    ) : (<div></div>)}
                </div>
            </div>
            <div className="overflow-scroll" style={{maxHeight: "50vh",}}>
                <table className="table table-bordered">
                    <thead className="sticky-top table-secondary">
                        <tr>
                            <th className="fw-normal">Nombre</th>
                            <th className="fw-normal">Ordenanza</th>
                            <th className="fw-normal">Implementacion</th>
                            <th className="fw-normal">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    {carreras.map((carrera) => (
                        <tr key={carrera.id}>
                            <td className="fw-light">
                                {carrera.nombre}
                            </td>
                            <td className="fw-light">
                                {carrera.nroOrdenanza}
                            </td>
                            <td className="fw-light">
                                {carrera.nroImplementacion}
                            </td>
                            <td className="fw-light">
                                <DropdownAcciones
                                    rowId={carrera.id}
                                    opciones={isSearching ? [
                                        {
                                            label: 'Asociar electivo',
                                            onClick: () => asociarElectivo(carrera)
                                        },
                                        {
                                            label: 'Asociar obligatorio',
                                            onClick: () => asociarObligatorio(carrera)
                                        }
                                    ] : [
                                        {
                                            label: 'Ver',
                                            onClick: () => window.location.href = `/carrera/${carrera.id}`
                                        },
                                        {
                                            label: 'Desasociar',
                                            onClick: () => desasociar(carrera)
                                        }
                                    ]}
                                />
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}