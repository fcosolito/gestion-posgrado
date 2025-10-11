import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';

import { createRoot } from "react-dom/client";
import Listado from "./components/Listado";

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("listado-cursos");
  if (el) {
    const root = createRoot(el);
    root.render(<Listado />);
  }
});