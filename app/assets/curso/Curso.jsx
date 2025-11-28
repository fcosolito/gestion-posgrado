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

    const handleEliminar = () => {
        const form = document.getElementById("curso-delete-form");

        window.mostrarModalEliminar(
            "Confirmar eliminación",
            "¿Esta seguro de que desea eliminar el curso?",
            `${curso.nombre}`,
            () => form.submit()
        );
    }

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
        <div className="container-fluid p-3 cuadrado-reutilizable">
            <div className="row">
                <div className="col">
                    <h4>Curso</h4>
                </div>
                <div className="col-auto">
                    {isEditing ? (
                    <div className="row">
                        <div className="col">
                            <button className="btn-verde" onClick={() => handleSave()}>Guardar</button>
                        </div>
                        <div className="col">
                            <button className="btn-rojo" onClick={() => handleCancel()}>Cancelar</button>
                        </div>
                    </div>
                    ) : (
                    <div className="row">
                        <div className="col">
                            <button className="btn-gris" onClick={() => window.location.href = "/curso"}>Volver</button>
                        </div>
                        <div className="col">
                            <button className="btn-amarillo" onClick={() => handleEdit(curso)}>Editar</button>
                        </div>
                        <div className="col">
                            <button className="btn-rojo" onClick={handleEliminar}>Eliminar</button>
                        </div>
                    </div>
                    )}
                </div>
            </div>
            {isEditing ? (
                <div className="row m-1">
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
                        <span className="fw-normal">Ordenanza</span>
                        <br></br>
                        <input 
                            type="text"
                            value={editValues["nroOrdenanza"]}
                            onChange={(e) => handleChange("nroOrdenanza", e.target.value)}
                            className="border"
                        />
                    </div>
                    <div className="col-2">
                        <span className="fw-normal">Implementacion</span>
                        <br></br>
                        <input 
                            type="text"
                            value={editValues["nroImplementacion"]}
                            onChange={(e) => handleChange("nroImplementacion", e.target.value)}
                            className="border"
                        />
                    </div>
                    <div className="col-2">
                        <span className="fw-normal">Horas</span>
                        <br></br>
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
                    <div className="col-2">
                        <span className="fw-normal">Nombre</span>
                        <br></br>
                        <span className="fw-light">{curso.nombre}</span>
                    </div>
                    <div className="col-2">
                        <span className="fw-normal">Ordenanza</span>
                        <br></br>
                        <span className="fw-light">{curso.nroOrdenanza}</span>
                    </div>
                    <div className="col-2">
                        <span className="fw-normal">Implementacion</span>
                        <br></br>
                        <span className="fw-light">{curso.nroImplementacion}</span>
                    </div>
                    <div className="col-2">
                        <span className="fw-normal">Horas</span>
                        <br></br>
                        <span className="fw-light">{curso.horas}</span>
                    </div>
                </div>

            )}

            <div 
                hidden
                dangerouslySetInnerHTML={{ __html: deleteFormHtml }}
            >
            </div>
        </div>

    );
}