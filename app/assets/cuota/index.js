import { createRoot } from "react-dom/client";
import { useState } from 'react';

import ListaCuotas from './ListaCuotas';
import DetalleCuotaPagos from './DetalleCuotaPagos';
import FormBuscarCuotas from "./FormBuscarCuotas";

export default function Index({ cuotas, carrera, curso, edicion, alumno }) {
    const [cuotaSel, setCuotaSel] = useState();

    return (
        <div className="container-fluid m-0 p-3">
            <div className="cuadrado-reutilizable p-3">
                <div className="row">
                    <div className="col">
                        <h4>Filtrar</h4>
                    </div>
                </div>
                <div className="row">
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
                <div className="col-7 pe-1" style={{ maxHeight: "80vh",}}>
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

    index_root.render(<Index 
            cuotas={cuotas} 
            carrera={carrera}
            curso={curso}
            edicion={edicion}
            alumno={alumno}
        />);
  }
  });