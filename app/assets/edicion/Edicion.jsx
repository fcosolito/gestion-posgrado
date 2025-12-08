import {useState} from 'react';

export default function Curso({edicion, inscripciones, modalEliminacion}) {
    const [isEditing, setIsEditing] = useState(false);
    const [editValues, setEditValues] = useState({});

    const handleEdit = (edicion) => {
        setIsEditing(true);
        setEditValues(edicion);
    };

    const handleCancel = () => {
        setIsEditing(false);
        setEditValues({});
    };

    const handleChange = (field, value) => {
        setEditValues((prev) => ({ ...prev, [field]: value }));
    };

    const handleSave = async () => {
        try {
            const res = await fetch(`/edicion/${edicion.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(editValues),
            });
        } catch (err) {
        }
        window.location.reload();
    };

    const handleDelete = () => {
        window.mostrarModalEliminarConInscripciones(edicion.nombre, inscripciones, modalEliminacion)
    }

    return (
        <div className="container cuadrado-reutilizable">
            <div className="row d-flex justify-content-start mb-3">
                <div className="col">
                    <h4>Edicion</h4>
                </div>
                <div className="col-auto">
                    {isEditing ? (
                    <div className="row d-flex justify-content-end">
                        <div className="col-auto">
                            <button className="btn-verde" onClick={() => handleSave()}>Guardar</button>
                        </div>
                        <div className="col-auto">
                            <button className="btn-rojo" onClick={() => handleCancel()}>Cancelar</button>
                        </div>
                    </div>
                    ) : (
                    <div className="row d-flex justify-content-end">
                        <div className="col-auto">
                            <button className="btn-alternativo" onClick={() => window.location.href = `/edicion/${edicion.id}/notas`}>Notas</button>
                        </div>
                        <div className="col-auto">
                            <button className="btn-amarillo" onClick={() => handleEdit(edicion)}>Editar</button>
                        </div>
                        <div className="col-auto">
                            <button className="btn-rojo" onClick={() => handleDelete()}>Eliminar</button>
                        </div>
                    </div>
                    )}
                </div>
            </div>
            
            {isEditing ? (
                <div className="row">
                    <div className="col">
                        <span className="fw-normal">Nombre</span>
                        <br></br>
                        <input
                            type="text"
                            value={editValues["nombre"]}
                            onChange={(e) => handleChange("nombre", e.target.value)}
                            className="border"
                        />
                    </div>
                    <div className="col">
                        <span className="fw-normal">Inicio</span>
                        <br></br>
                        <input 
                            type="date"
                            value={editValues["fechaInicio"]}
                            onChange={(e) => handleChange("fechaInicio", e.target.value)}
                            className="border"
                        />
                    </div>
                    <div className="col">
                        <span className="fw-normal">Fin</span>
                        <br></br>
                        <input 
                            type="date"
                            value={editValues["fechaFin"]}
                            onChange={(e) => handleChange("fechaFin", e.target.value)}
                            className="border"
                        />
                    </div>
                    <div className="col">
                        <span className="fw-normal">Precio</span>
                        <br></br>
                        <input 
                            type="number"
                            value={editValues["precio"]}
                            onChange={(e) => handleChange("precio", e.target.value)}
                            className="border"
                        />
                    </div>
                </div>
            ) : (
                <div className="row">
                    <div className="col">
                        <span className="fw-normal">Nombre</span>
                        <br></br>
                        <span className="fw-light">{edicion.nombre}</span>
                    </div>
                    <div className="col">
                        <span className="fw-normal">Inicio</span>
                        <br></br>
                        <span className="fw-light">{edicion.fechaInicio}</span>
                    </div>
                    <div className="col">
                        <span className="fw-normal">Fin</span>
                        <br></br>
                        <span className="fw-light">{edicion.fechaFin}</span>
                    </div>
                    <div className="col">
                        <span className="fw-normal">Precio</span>
                        <br></br>
                        <span className="fw-light">{edicion.precio}</span>
                    </div>
                </div>
            )}
        </div>

    );
}