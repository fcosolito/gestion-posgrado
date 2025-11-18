import '../styles/app.css';

import { createRoot } from "react-dom/client";
import Edicion from './Edicion';
import ListaInscripciones from './ListaInscripciones';

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("edicion");
  if (el) {
    const root = createRoot(el);
    const edicion = JSON.parse(el.dataset.edicion);
    const deleteForm = el.dataset.deleteForm;
    root.render(<Edicion edicion={edicion} deleteFormHtml={deleteForm} />);
  }

  const inscripciones_el = document.getElementById("listado-alumnos");
  if (inscripciones_el) {
    const insc_root = createRoot(inscripciones_el);
    const alumnos = JSON.parse(inscripciones_el.dataset.alumnos);
    const descuentos = JSON.parse(inscripciones_el.dataset.descuentos);
    insc_root.render(<ListaInscripciones alumnos={alumnos} descuentos={descuentos} />)
  }
});