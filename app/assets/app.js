/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'bootstrap/dist/js/bootstrap.min.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

import { createRoot } from "react-dom/client";
import Sidebar from "./components/Sidebar.jsx";

const el = document.getElementById("sidebar-root");
if (el) {
  const initialPage = el.dataset.page || "/alumno"; // Symfony puede inyectar datos acá
  const root = createRoot(el);
  root.render(<Sidebar initialPage={initialPage} />);
}



