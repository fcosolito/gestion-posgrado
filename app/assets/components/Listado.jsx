export default function Listado() {
    const filas = [
        {
            id: 1,
            nombre: "franco",
            apellido: "cosolito"
        },
        {
            id: 2,
            nombre: "enzo",
            apellido: "garello"
        },
        {
            id: 3,
            nombre: "valentino",
            apellido: "carlozzi"
        },
    ];

    return (
        <div>
            <table>
                <thead>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Acciones</th>
                </thead>
                <tbody>
                    {filas.map(fila => {
                        <tr>
                            <td>{fila.nombre}</td>
                            <td>{fila.apellido}</td>
                            <td>...</td>
                        </tr>
                    })}
                </tbody>

            </table>
        </div>
    )
}