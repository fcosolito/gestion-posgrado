/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'bootstrap/dist/js/bootstrap.min.js';
import './styles/app.scss';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

import { createRoot } from "react-dom/client";
import { Modal } from 'bootstrap';
import Sidebar from "./components/Sidebar.jsx";
import Lista from "./components/Lista.jsx";
import ListaCuotas from "./components/ListaCuotas.jsx";
import ListaInscripciones from "./components/ListaInscripciones.jsx";

const el = document.getElementById("sidebar-root");
if (el) {
  const initialPage = el.dataset.page || "/alumno"; // Symfony puede inyectar datos acá
  const root = createRoot(el);
  root.render(<Sidebar initialPage={initialPage} />);
}

// Variables globales para el modal de confirmación reutilizable
let confirmModal = null;
let currentAction = null; // Función que se ejecutará al confirmar

// Función auxiliar para mostrar el modal de confirmación
function mostrarModalConfirmacion(titulo, mensaje, detalle, onConfirm) {
  // Actualizar título
  const tituloElement = document.querySelector('#deleteModal .modal-title');
  if (tituloElement) {
    tituloElement.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${titulo}`;
  }
  
  // Actualizar mensaje
  const mensajeElements = document.querySelectorAll('#deleteModal .modal-body p');
  if (mensajeElements.length >= 1) {
    mensajeElements[0].textContent = mensaje;
  }
  
  // Actualizar detalle
  const detalleElement = document.getElementById('alumnoNombreModal');
  if (detalleElement) {
    detalleElement.textContent = detalle;
  }
  
  // Guardar la acción a ejecutar
  currentAction = onConfirm;
  
  // Mostrar el modal
  if (confirmModal) {
    confirmModal.show();
  }
}

document.addEventListener("DOMContentLoaded", () => {
  // Inicializar el modal de confirmación si existe
  const confirmModalElement = document.getElementById('deleteModal');
  if (confirmModalElement) {
    confirmModal = new Modal(confirmModalElement);
    
    // Evento para confirmar acción
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', () => {
      if (currentAction) {
        currentAction();
        currentAction = null;
      }
      confirmModal.hide();
    });
  }

  const listaDiv = document.getElementById("lista-generica");
  if (listaDiv) {
    const labels = JSON.parse(listaDiv.dataset.labels || '[]');
    const attributes = JSON.parse(listaDiv.dataset.attributes || '[]');
    const rows = JSON.parse(listaDiv.dataset.rows || '[]');
    
    // Definir las opciones de acciones para la lista de alumnos
    const opcionesAcciones = [
      {
        label: 'Visualizar/Editar',
        onClick: (rowIndex) => {
          const alumnoId = rows[rowIndex].id;
          window.location.href = `/alumno/${alumnoId}/visualizar`;
        }
      },
      {
        label: 'Eliminar',
        onClick: (rowIndex) => {
          const alumno = rows[rowIndex];
          const alumnoNombre = `${alumno.nombre} ${alumno.apellido}`;
          
          mostrarModalConfirmacion(
            'Confirmar eliminación',
            '¿Está seguro de que desea eliminar al alumno?',
            alumnoNombre,
            () => {
              const container = document.getElementById(`delete-form-${alumno.id}`);
              if (container) {
                const form = container.querySelector('form');
                if (form) {
                  form.submit();
                }
              }
            }
          );
        }
      },
    ];
    
    const root = createRoot(listaDiv);
    root.render(<Lista labels={labels} attributes={attributes} rows={rows} opcionesAcciones={opcionesAcciones} />);
  }

  const listaCarreras = document.getElementById("lista-carreras");
  if (listaCarreras) {
    const labels = JSON.parse(listaCarreras.dataset.labels || '[]');
    const attributes = JSON.parse(listaCarreras.dataset.attributes || '[]');
    const rows = JSON.parse(listaCarreras.dataset.rows || '[]');
    
    // Definir las opciones de acciones para la lista de carreras
    const opcionesAcciones = [
      {
        label: 'a implementar',
        onClick: (rowIndex) => {
          const carreraId = rows[rowIndex].id;
          console.log('Ver carrera:', carreraId);
          // logica
        }
      },
    ];
    
    const root = createRoot(listaCarreras);
    root.render(<Lista labels={labels} attributes={attributes} rows={rows} opcionesAcciones={opcionesAcciones} />);
  }

  const listaCursos = document.getElementById("lista-cursos");
  if (listaCursos) {
    const labels = JSON.parse(listaCursos.dataset.labels || '[]');
    const attributes = JSON.parse(listaCursos.dataset.attributes || '[]');
    const rows = JSON.parse(listaCursos.dataset.rows || '[]');
    
    // Definir las opciones de acciones para la lista de cursos
    const opcionesAcciones = [
      {
        label: 'a implementar',
        onClick: (rowIndex) => {
          const cursoId = rows[rowIndex].id;
          console.log('Ver curso:', cursoId);
          // logica
        }
      },
    ];
    
    const root = createRoot(listaCursos);
    root.render(<Lista labels={labels} attributes={attributes} rows={rows} opcionesAcciones={opcionesAcciones} />);
  }

  const listaCuotas = document.getElementById("lista-cuotas");
  if (listaCuotas) {
    const cuotas = JSON.parse(listaCuotas.dataset.cuotas || '[]');
    const root = createRoot(listaCuotas);
    root.render(<ListaCuotas cuotas={cuotas} />);
  }

  const listaInscripciones = document.getElementById("lista-inscripciones");
  if (listaInscripciones) {
    const labels = JSON.parse(listaInscripciones.dataset.labels || '[]');
    const attributes = JSON.parse(listaInscripciones.dataset.attributes || '[]');
    const rows = JSON.parse(listaInscripciones.dataset.rows || '[]');
    const alumnoId = listaInscripciones.dataset.alumnoId;
    
    const handleAccionClick = (row) => {
      if (row.accion === 'Inscribir') {
        // Crear formulario POST para inscribir
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/alumno/${alumnoId}/inscribir-carrera/${row.id}`;
        document.body.appendChild(form);
        form.submit();
      } else if (row.accion === 'Borrar') {
        // Mostrar modal de confirmación antes de desinscribir
        mostrarModalConfirmacion(
          'Confirmar desinscripción',
          '¿Está seguro que desea eliminar la inscripción de la carrera?',
          row.nombre,
          () => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/alumno/${alumnoId}/desinscribir-carrera/${row.id}`;
            document.body.appendChild(form);
            form.submit();
          }
        );
      }
    };
    
    const root = createRoot(listaInscripciones);
    root.render(<ListaInscripciones labels={labels} attributes={attributes} rows={rows} onAccionClick={handleAccionClick} />);
  }

});
