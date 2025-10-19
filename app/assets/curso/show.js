import '../styles/app.scss';
import 'bootstrap/dist/js/bootstrap.min.js';

import { createRoot } from "react-dom/client";
import Curso from './Curso';

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("curso");
  if (el) {
    const root = createRoot(el);
    const curso = JSON.parse(el.dataset.curso);
    const deleteForm = el.dataset.deleteForm;
    root.render(<Curso curso={curso} deleteFormHtml={deleteForm} />);
  }
});