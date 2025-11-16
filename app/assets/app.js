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
import Lista from "./components/Lista.jsx";
import { Modal } from 'bootstrap';

const el = document.getElementById("sidebar-root");
if (el) {
  const initialPage = el.dataset.page || "/alumno"; // Symfony puede inyectar datos acá
  const root = createRoot(el);
  root.render(<Sidebar initialPage={initialPage} />);
}

// Variables globales para el modal de confirmación reutilizable
let confirmModal = null;
let currentAction = null;

// Función auxiliar para mostrar el modal de confirmación
// Exportada globalmente para que otros módulos puedan usarla
window.mostrarModalConfirmacion = function(titulo, mensaje, detalle, onConfirm) {
  const tituloElement = document.querySelector('#deleteModal .modal-title');
  if (tituloElement) {
    tituloElement.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${titulo}`;
  }
  
  const mensajeElements = document.querySelectorAll('#deleteModal .modal-body p');
  if (mensajeElements.length >= 1) {
    mensajeElements[0].textContent = mensaje;
  }
  
  const detalleElement = document.getElementById('detalleElementoModal');
  if (detalleElement) {
    detalleElement.textContent = detalle;
  }
  
  currentAction = onConfirm;
  
  if (confirmModal) {
    confirmModal.show();
  }
};

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  // Inicializar el modal de confirmación si existe
  const confirmModalElement = document.getElementById('deleteModal');
  if (confirmModalElement) {
    confirmModal = new Modal(confirmModalElement);
    
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', () => {
      if (currentAction) {
        currentAction();
        currentAction = null;
      }
      confirmModal.hide();
    });
  }

  // Renderizar lista de notas (funciona tanto para alumno como para edición)
  const listaNotas = document.getElementById("lista-notas");
  if (listaNotas) {
    const labels = JSON.parse(listaNotas.dataset.labels || '[]');
    const attributes = JSON.parse(listaNotas.dataset.attributes || '[]');
    const rows = JSON.parse(listaNotas.dataset.rows || '[]');
    
    const opcionesAcciones = [
      {
        label: 'Editar (a implementar)',
        onClick: (rowIndex) => {
          // Implementar edición
        }
      },
      {
        label: 'Eliminar',
        onClick: (rowIndex) => {
          const nota = rows[rowIndex];
          const datosNota = `${nota.nota} | ${nota.descripcion || ''} cargada el ${nota.fecha_carga}`;
          
          mostrarModalConfirmacion(
            'Confirmar eliminación',
            '¿Está seguro de que desea eliminar la nota?',
            datosNota,
            () => {
              const container = document.getElementById(`delete-form-${nota.id}`);
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
    
    const root = createRoot(listaNotas);
    root.render(<Lista labels={labels} attributes={attributes} rows={rows} opcionesAcciones={opcionesAcciones} />);
  }
});


