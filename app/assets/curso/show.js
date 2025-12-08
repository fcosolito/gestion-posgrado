import { createRoot } from "react-dom/client";
import Curso from './Curso';
import Carreras from './Carreras';
import Modal from 'bootstrap/js/dist/modal';

document.addEventListener("DOMContentLoaded", () => {
  const curso_el = document.getElementById("curso");
  if (curso_el) {
    const curso_root = createRoot(curso_el);
    const curso = JSON.parse(curso_el.dataset.curso);
    const ediciones = JSON.parse(curso_el.dataset.ediciones);
    const modal = new Modal(document.getElementById('modalEliminacionEdiciones'));
    curso_root.render(<Curso curso={curso} ediciones={ediciones} modalEliminacion={modal} />);

    const carreras_el = document.getElementById("carreras");
    if (carreras_el) {
      const carreras_root = createRoot(carreras_el);
      const carreras = JSON.parse(carreras_el.dataset.carreras);
      carreras_root.render(<Carreras asociadas={carreras} curso={curso} />);
    }
  }

  });

window.mostrarModalEliminarConEdiciones = function(nombre, ediciones, modal) {
    const form = document.getElementById("curso-delete-form");

    // Actualizar título
    const nombreElement = document.getElementById('modalEdicionesCursoNombre');
    if (nombreElement) {
        nombreElement.textContent = nombre;
    }
    
    // Generar lista de ediciones
    const listaEdiciones = document.getElementById('listaEdicionesEliminar');
    if (listaEdiciones) {
        listaEdiciones.innerHTML = '';
        
        let tienePagos = false;
        ediciones.forEach(edicion => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            
            li.innerHTML = `
                Edicion ${edicion.nombre}
                <span class="badge bg-secondary">${edicion.inscripcionesCount} insc.</span>
            `;
            listaEdiciones.appendChild(li);
        });
        
        // Actualizar el texto de eliminación según el tipo
        const textoEliminacion = document.getElementById('textoEliminacion');
        if (textoEliminacion) {
          textoEliminacion.innerHTML = '<i class="bi bi-info-circle me-1"></i>Al eliminar el curso, se eliminarán todas las <strong>ediciones</strong> asociadas, sus registros de <strong>inscripciones</strong> e información asociada a estas (notas, cuotas, legajo).';
        }
    }
    
    // Configurar botón de confirmación
    const btnConfirmar = document.getElementById('btnConfirmarEliminacionEdiciones');
    if (btnConfirmar) {
        btnConfirmar.onclick = function() {
          form.submit();
        };
    }
    
    modal.show();
};