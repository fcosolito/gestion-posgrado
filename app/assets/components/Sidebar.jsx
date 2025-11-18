export default function Sidebar({ initialPage }) {

    const items = [
        {
            name: "Alumnos",
            path: "/alumno"
        },
        {
            name: "Carreras",
            path: "/carrera"
        },
        {
            name: "Cursos",
            path: "/curso"
        },
        {
            name: "Cuotas",
            path: "/cuota"
        },
        {
            name: "Pagos",
            path: "/pago"
        },
        {
            name: "Descuentos",
            path: "/descuento"
        },
    ];

    return (
        <div className="d-flex flex-column flex-shrink-0 p-3 bg-white text-black" style={{ width: "220px", minHeight: "100vh" }}>
                <a href="/" className="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-black text-decoration-none">
                    <img 
                        src="/images/arana_utn.png"
                        alt="Logo"
                        className="img-fluid me-2"
                        style={{ maxWidth: "40px" }}
                    />
                    <div className="vr amarillo-principal mx-3"></div>
                    <span className="fs-4">Posgrado</span>
                </a>
                <hr />
                <ul className="nav nav-pills flex-column ">
                    {items.map( item => (
                        <li key={item.path} className="nav-item">

                            <a 
                                href={item.path} 
                                className={"nav-link text-black " + (initialPage === item.path ? "bg-grey" : "")}
                            >
                                {item.name}
                            </a>

                        </li>
                    ))}
                </ul>
            </div>
    );
}