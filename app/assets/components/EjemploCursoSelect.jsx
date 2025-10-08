import React, { useEffect, useState } from "react";

export default function EjemploCursoSelect({ fetchUrl }) {
    const [curso, setCurso] = useState("");
    const [dictados, setDictados] = useState([]);
    const [dictado, setDictado] = useState("");

    const cursoSelect = document.getElementById("ejemplo_curso_curso");
    const hiddenDictado = document.getElementById("ejemplo_curso_dictado");

    useEffect(() => {
        // Actualiza curso cuando cambia el select de Symfony
        cursoSelect.addEventListener("change", (e) => {
        console.log("curso seteado a " + e.target.value);
        setCurso(e.target.value);
        setDictado("");
        });

        // Actualiza el campo hidden cada vez que cambia el dictado
        hiddenDictado.value = dictado;
        console.log("hidden value: " + hiddenDictado.value);
    }, [dictado]);

    // Fetch de dictados al cambiar curso
    useEffect(() => {
        if (!curso) {
        setDictados([]);
        return;
        }

        fetch(`${fetchUrl}1`)
        .then((res) => res.json())
        .then((data) => setDictados(data))
        .catch(() => setDictados([]));
        console.log("Dictados: \n");
        console.log(dictados);
    }, [curso]);

    return (
        <div className="mt-3">
        <label className="form-label">Dictado</label>
        <select
            className="form-select"
            value={dictado}
            onChange={(e) => setDictado(e.target.value)}
            disabled={!curso}
        >
            <option value="">Seleccione un dictado</option>
            {dictados.map((d) => (
            <option key={d.id} value={d.id}>
                {d.nombre}
            </option>
            ))}
        </select>
        </div>
    );
}