import '../styles/app.css';

import { createRoot } from "react-dom/client";
import Edicion from './Edicion';
import ListaInscripciones from './ListaInscripciones';
import ListaDocentes from './ListaDocentes';
import Modal from 'bootstrap/js/dist/modal';

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("edicion");
  if (el) {
    const root = createRoot(el);
    const edicion = JSON.parse(el.dataset.edicion);
    const inscripciones = JSON.parse(el.dataset.alumnos);
    const modal = new Modal(document.getElementById('modalEliminacionInscripciones'));
    root.render(<Edicion edicion={edicion} inscripciones={inscripciones} modalEliminacion={modal} />);
  }

  const inscripciones_el = document.getElementById("listado-alumnos");
  if (inscripciones_el) {
    const insc_root = createRoot(inscripciones_el);
    const edicion = JSON.parse(inscripciones_el.dataset.edicion);
    const alumnos = JSON.parse(inscripciones_el.dataset.alumnos);
    const descuentos = JSON.parse(inscripciones_el.dataset.descuentos);
    insc_root.render(<ListaInscripciones edicion={edicion} alumnos={alumnos} descuentos={descuentos} />)
  }

  const docentes_el = document.getElementById("listado-docentes");
  if (docentes_el) {
    const docentes_root = createRoot(docentes_el);
    const docentes = JSON.parse(docentes_el.dataset.docentes);

    docentes_root.render(<ListaDocentes docentes={docentes} edicion={edicion} />)
  }
});

window.mostrarModalEliminarConInscripciones = function(nombre, inscripciones, modal) {
    const form = document.getElementById("edicion-delete-form");

    // Actualizar título
    const nombreElement = document.getElementById('modalInscripcionesEdicionNombre');
    if (nombreElement) {
        nombreElement.textContent = nombre;
    }
    
    // Generar lista de ediciones
    const listaInscripciones = document.getElementById('listaInscripcionesEliminar');
    if (listaInscripciones) {
        listaInscripciones.innerHTML = '';
        
        let tieneNotas = false;
        inscripciones.forEach(insc => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            
            li.innerHTML = `
                Alumno ${insc.nombre} ${insc.apellido}
                <span class="badge bg-secondary">${insc.notasCount} notas</span>
            `;
            listaInscripciones.appendChild(li);
        });
        
        // Actualizar el texto de eliminación según el tipo
        const textoEliminacion = document.getElementById('textoEliminacion');
        if (textoEliminacion) {
          textoEliminacion.innerHTML = '<i class="bi bi-info-circle me-1"></i>Al eliminar la edición, se eliminarán todas las <strong>inscripciones</strong> asociadas e información asociada a estas (notas, cuotas, legajo).';
        }
    }
    
    // Configurar botón de confirmación
    const btnConfirmar = document.getElementById('btnConfirmarEliminacionInscripciones');
    if (btnConfirmar) {
        btnConfirmar.onclick = function() {
          form.submit();
        };
    }
    
    modal.show();
};