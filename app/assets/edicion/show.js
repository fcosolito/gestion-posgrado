import '../styles/app.css';

import { createRoot } from "react-dom/client";
import Edicion from './Edicion';
import ListaInscripciones from './ListaInscripciones';
import ListaDocentes from './ListaDocentes';

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

  const docentes_el = document.getElementById("listado-docentes");
  if (docentes_el) {
    const docentes_root = createRoot(docentes_el);
    const docentes = JSON.parse(docentes_el.dataset.docentes);
    const edicion = JSON.parse(docentes_el.dataset.edicion);
    docentes_root.render(<ListaDocentes docentes={docentes} edicion={edicion} />)
  }
});