import {useState} from 'react';

export default function Curso({edicion, deleteFormHtml}) {
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

    return (
        <>
            <div className="row d-flex justify-content-start">
                <div className="col">
                    {isEditing ? (
                        <input
                            type="text"
                            value={editValues["nombre"]}
                            onChange={(e) => handleChange("nombre", e.target.value)}
                            className="border"
                        />
                    ) : (
                        <span className="m-1 fs-5 fw-bold">{edicion.nombre}</span>
                    )}
                </div>
            </div>
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
                <div className="col-auto"
                    dangerouslySetInnerHTML={{ __html: deleteFormHtml }}
                >
                </div>
            </div>
            )}
            <div className="row m-1">
                <div className="col-4 fw-bold">Inicio</div>
                <div className="col-4 fw-bold">Fin</div>
                <div className="col-4 fw-bold">Precio</div>
            </div>
            {isEditing ? (
                <div className="row m-1">
                    <div className="col-4">
                        <input 
                            type="date"
                            value={editValues["fechaInicio"]}
                            onChange={(e) => handleChange("fechaInicio", e.target.value)}
                            className="border"
                        />
                    </div>
                    <div className="col-4">
                        <input 
                            type="date"
                            value={editValues["fechaFin"]}
                            onChange={(e) => handleChange("fechaFin", e.target.value)}
                            className="border"
                        />
                    </div>
                    <div className="col-4">
                        <input 
                            type="number"
                            value={editValues["precio"]}
                            onChange={(e) => handleChange("precio", e.target.value)}
                            className="border"
                        />
                    </div>
                </div>
            ) : (
                <div className="row m-1">
                    <div className="col-4">{edicion.fechaInicio}</div>
                    <div className="col-4">{edicion.fechaFin}</div>
                    <div className="col-4">{edicion.precio}</div>
                </div>
            )}
        </>

    );
}