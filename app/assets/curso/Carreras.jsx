import {useState} from "react";

export default function Carreras ({ asociadas }){
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

                if (!res.ok) throw new Error("Error al buscar");

                const data = await res.json();
                setCarreras(data);
            } catch (err) {
                alert(err.message);
            }
    }

    return (
        <div className="container">
            <div className="row">
                <div className="col">
                    <h3>Carreras</h3>
                </div>
                <div className="col">
                    {isSearching ? (
                        <button
                            className="btn btn-danger"
                            onClick={() => handleCancel()}
                        >Cancelar</button>

                    ) : (
                         <button
                            className="btn btn-primary"
                            onClick={() => handleSearch()}
                        >Asociar carrera</button>

                    )}
                </div>
            </div>
            <div className="row">
                <div className="col">
                    {isSearching ? (
                        <input
                            type="text"
                            className=""
                            onChange={(e) => handleChange(e.target.value)}
                        />
                    ) : (<div></div>)}
                </div>
            </div>
            <div className="row bg-grey">
                <div className="col">
                    Nombre
                </div>
                <div className="col">
                    Ord
                </div>
                <div className="col">
                    Impl
                </div>
                <div className="col">
                    Acciones
                </div>
            </div>

            {carreras.map((carrera) => (
                <div className="row">
                    <div className="col">
                        {carrera.nombre}
                    </div>
                    <div className="col">
                        {carrera.nroOrdenanza}
                    </div>
                    <div className="col">
                        {carrera.nroImplementacion}
                    </div>
                    <div className="col">
                        Acciones
                    </div>
                </div>
            ))}
        </div>
    );
}