import {useState} from 'react';

export default function Curso({curso, deleteFormHtml}) {
    const [isEditing, setIsEditing] = useState(false);
    const [editValues, setEditValues] = useState({});

    const handleEdit = (curso) => {
        setIsEditing(true);
        setEditValues(curso);
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
            const res = await fetch(`/curso/${curso.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(editValues),
            });
        } catch (err) {
        }
        window.location.reload();
    };

    return (
        <div className="container-fluid p-2 rounded bg-white">
            <div className="row">
                <div className="col-4">
                    {isEditing ? (
                        <input
                            type="text"
                            value={editValues["nombre"]}
                            onChange={(e) => handleChange("nombre", e.target.value)}
                            className="border"
                        />

                    ) : (
                        <h2 className="m-1">{curso.nombre}</h2>
                    )}
                </div>
                <div className="col-3 ms-auto">
                    {isEditing ? (
                    <div className="row">
                        <div className="col">
                            <button className="btn btn-primary" onClick={() => handleSave()}>Guardar</button>
                        </div>
                        <div className="col">
                            <button className="btn btn-danger" onClick={() => handleCancel()}>Cancelar</button>
                        </div>
                    </div>
                    ) : (
                    <div className="row">
                        <div className="col">
                            <button className="btn btn-secondary" onClick={() => handleEdit(curso)}>Editar</button>
                        </div>
                        <div className="col"
                            dangerouslySetInnerHTML={{ __html: deleteFormHtml }}
                        >
                        </div>
                    </div>
                    )}
                </div>
            </div>
            <div className="row m-1">
                <div className="col-2 fw-bold">Ordenanza</div>
                <div className="col-2 fw-bold">Implementacion</div>
                <div className="col-2 fw-bold">Horas</div>
            </div>
            {isEditing ? (
                <div className="row m-1">
                    <div className="col-2">
                        <input 
                            type="text"
                            value={editValues["nroOrdenanza"]}
                            onChange={(e) => handleChange("nroOrdenanza", e.target.value)}
                            className="border"
                        />
                    </div>
                    <div className="col-2">
                        <input 
                            type="text"
                            value={editValues["nroImplementacion"]}
                            onChange={(e) => handleChange("nroImplementacion", e.target.value)}
                            className="border"
                        />
                    </div>
                    <div className="col-2">
                        <input 
                            type="text"
                            value={editValues["horas"]}
                            onChange={(e) => handleChange("horas", e.target.value)}
                            className="border"
                        />
                    </div>
                </div>
            ) : (
                <div className="row m-1">
                    <div className="col-2">{curso.nroOrdenanza}</div>
                    <div className="col-2">{curso.nroImplementacion}</div>
                    <div className="col-2">{curso.horas}</div>
                </div>

            )}
        </div>

    );
}