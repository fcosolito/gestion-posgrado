import '../styles/app.scss';
import 'bootstrap/dist/js/bootstrap.min.js';

import { createRoot } from "react-dom/client";
import Curso from './Curso';
import Carreras from './Carreras';

document.addEventListener("DOMContentLoaded", () => {
  const curso_el = document.getElementById("curso");
  if (curso_el) {
    const curso_root = createRoot(curso_el);
    const curso = JSON.parse(curso_el.dataset.curso);
    const deleteForm = curso_el.dataset.deleteForm;
    curso_root.render(<Curso curso={curso} deleteFormHtml={deleteForm} />);

    const carreras_el = document.getElementById("carreras");
    if (carreras_el) {
      const carreras_root = createRoot(carreras_el);
      const carreras = JSON.parse(carreras_el.dataset.carreras);
      carreras_root.render(<Carreras asociadas={carreras} curso={curso} />);
    }
  }

  });