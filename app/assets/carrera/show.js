import '../styles/app.css';

import { createRoot } from "react-dom/client";
import ListaAlumnos from './ListaAlumnos';

document.addEventListener("DOMContentLoaded", () => {
  const alumnos_el = document.getElementById("listado-alumnos");
  if (alumnos_el) {
    const root = createRoot(alumnos_el);
    const alumnos = JSON.parse(alumnos_el.dataset.alumnos);
    const descuentos = JSON.parse(alumnos_el.dataset.descuentos);
    const carrera = JSON.parse(alumnos_el.dataset.carrera);
    root.render(<ListaAlumnos carrera={carrera} alumnos={alumnos} descuentos={descuentos} />);
  }


});