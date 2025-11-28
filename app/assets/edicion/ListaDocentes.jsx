import { useState } from 'react';
import SelectorDropdown from '../components/SelectorDropdown';
import BuscadorDropdown from '../components/BuscadorDropdown';

export default function ListaDocentes ({ docentes, edicion }) {
    const [editingRow, setEditingRow] = useState(null);
    const [editValues, setEditValues] = useState([]);
    const [docente, setDocente] = useState(null);
    const [firmante, setFirmante] = useState(false);

    function handleEdit(docente) {
        setEditingRow(docente.id);
        setEditValues(docente);
    }

    function handleCancel(){
        setEditingRow(null);
        setEditValues({});
    }

    const handleChange = (field, value) => {
        setEditValues((prev) => ({ ...prev, [field]: value }));
    };

    // Modificar la asociacion de un docente editandolo
    const handleSave = async (id) => {
        try {
            const res = await fetch(`/edicion/${edicion.id}/asoc-docente/${editValues.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ esFirmante: editValues.esFirmante}),
            });
        } catch (err) {
        }
        setEditingRow(null);
        setEditValues({});
        window.location.reload();
    }
    
    // Asociar un nuevo docente usando el buscador
    const handleAsociar = async () => {
        try {
            const res = await fetch(`/edicion/${edicion.id}/asoc-docente/${docente.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ esFirmante: firmante}),
            });
        } catch (err) {
        }
        window.location.reload();
    }

    // Desasociar docente de la edicion
    const handleDesasociar = async (id) => {
        try {
            const res = await fetch(`/edicion/${edicion.id}/desasoc-docente/${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            });
        } catch (err) {
        }
        window.location.reload();
    }
    const buscarDocentes = async (query) => {
        return await fetch(`/docente/search?query=${query}`, {
                method: "GET",
                headers: { "Content-Type": "application/json" },
                });
    }

    return (
        <div className="cuadrado-reutilizable h-100 fill-remaining">
                <div className="row p-3 d-flex justify-content-between">
                    <div className="col-3 d-flex align-items-center justify-content-start">
                        <h4>Docentes | Asociar docente</h4>
                    </div>
                    <div className="col d-flex align-items-center justify-content-end">
                        <div className="row align-items-end">
                            <div className="col-auto">
                                <span className="fw-normal ms-1">Docente</span>
                                <br></br>
                                <BuscadorDropdown 
                                    fetchItems={buscarDocentes} 
                                    placeholder={"Buscar docente..."}
                                    setItem={setDocente}
                                    getId={docente => docente.id}
                                    getLabel={docente => `${docente.nombre} ${docente.apellido}`}
                                    getDetalle={docente => docente.dni}
                                />
                            </div>
                            <div className="col">
                                <button 
                                    className="btn btn-verde"
                                    onClick={handleAsociar}
                                >
                                    Asociar
                                </button>
                            </div>
                            <div className="col">
                            </div>
                            <div className="col">
                                <button 
                                    className="btn btn-verde"
                                    onClick={() => window.location.href = `/docente/new`}
                                >
                                    Nuevo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="h-25 overflow-scroll">
                    <table className="table table-bordered ">
                        <thead className="sticky-top table-secondary">
                            <tr>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>DNI</th>
                                <th>Firmante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {docentes.map((docente) => (
                                <tr
                                    key={docente.id}
                                >
                                    <td>{docente.nombre}</td>
                                    <td>{docente.apellido}</td>
                                    <td>{docente.dni}</td>
                                    <td>
                                    {editingRow === docente.id ? (
                                        <div className="form-check">
                                            <input className="form-check-input" 
                                                type="checkbox" 
                                                onChange={(e) => handleChange("esFirmante", e.target.checked)}
                                                disabled={false}
                                            >
                                            </input>
                                        </div>
                                    ) : (
                                        <div className="form-check">
                                            <input className="form-check-input" 
                                                type="checkbox" 
                                                disabled={true}
                                                checked={docente.esFirmante ? true : false}
                                            >
                                            </input>
                                        </div>
                                    )}
                                    </td>
                                    <td>
                                        {editingRow === docente.id ? (
                                            <div className="row">
                                                <div className="col-auto">
                                                    <button
                                                        onClick={() => handleSave(docente.id)}
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
                                                        <a className="dropdown-item btn btn-secondary" href={`/docente/${docente.id}`}>
                                                            Ver
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button
                                                            className="dropdown-item btn btn-secondary"
                                                            onClick={() => handleEdit(docente)}
                                                        >
                                                            Editar
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button
                                                            className="dropdown-item btn btn-secondary"
                                                            onClick={() => handleDesasociar(docente.id)}
                                                        >
                                                            Desasociar
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