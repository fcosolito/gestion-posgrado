import { createRoot } from "react-dom/client";
import { useState } from 'react';

import ListaCuotas from './ListaCuotas';
import DetalleCuotaPagos from './DetalleCuotaPagos';
import FormBuscarCuotas from "./FormBuscarCuotas";

export default function Index({ cuotas, carrera, curso, edicion, alumno }) {
    const [cuotaSel, setCuotaSel] = useState();

    return (
        <div className="container-fluid m-0">
            <div className="bg-white rounded p-2 mt-2">
                <div className="row mt-2">
                    <div className="col-1">
                        <h3>Filtrar</h3>
                    </div>
                    <div className="col">
                        <FormBuscarCuotas 
                            carreraIni={carrera}
                            cursoIni={curso}
                            edicionIni={edicion}
                            alumnoIni={alumno}
                        />
                    </div>
                </div>
            </div>
            <div className="row mt-2">
                <div className="col-7 pe-1 vh-100">
                    <ListaCuotas 
                        cuotas={cuotas}
                        onSelect={setCuotaSel}
                    />
                </div>
                <div className="col-5">
                    <DetalleCuotaPagos 
                        cuota={cuotaSel}
                    />
                </div>
            </div>
        </div>
    );
}

document.addEventListener("DOMContentLoaded", () => {
  const index_el = document.getElementById("cuota-index");
  const carrera = JSON.parse(index_el.dataset.carrera);
  const curso = JSON.parse(index_el.dataset.curso);
  const edicion = JSON.parse(index_el.dataset.edicion);
  const alumno = JSON.parse(index_el.dataset.alumno);

  if (index_el) {
    const index_root = createRoot(index_el);
    const cuotas = JSON.parse(index_el.dataset.cuotas);
    const cuotasEj = [
        {
            id: 0,
            inscripcionId: 0,
            descuento: 0.10,
            nombreAlumno: "Franco",
            apellidoAlumno: "Cosolito",
            dniAlumno: 43000000,
            valor: 25000,
            estado: "No paga",
            pagos: [
                {
                    id: 1,
                    monto: 30000,
                    fechaDePago: new Date("2025-08-01"),
                    comprobante: "comp_1.png"
                },
            ]

        },
    ]
    index_root.render(<Index 
            cuotas={cuotas} 
            carrera={carrera}
            curso={curso}
            edicion={edicion}
            alumno={alumno}
        />);
  }
  });