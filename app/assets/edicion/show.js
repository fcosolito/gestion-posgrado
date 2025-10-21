import '../styles/app.css';

import { createRoot } from "react-dom/client";
import Edicion from './Edicion';

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("edicion");
  if (el) {
    const root = createRoot(el);
    const edicion = JSON.parse(el.dataset.edicion);
    const deleteForm = el.dataset.deleteForm;
    root.render(<Edicion edicion={edicion} deleteFormHtml={deleteForm} />);
  }
});