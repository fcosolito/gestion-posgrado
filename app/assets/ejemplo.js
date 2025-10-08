import { createRoot } from "react-dom/client";
import EjemploCursoSelect from "./components/EjemploCursoSelect";

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("curso-select-root");
  if (el) {
    const apiBaseUrl = el.dataset.api;
    const root = createRoot(el);
    root.render(<EjemploCursoSelect fetchUrl={apiBaseUrl} />);
  }
});