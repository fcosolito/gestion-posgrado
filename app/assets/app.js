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
import Modal from 'bootstrap/js/dist/modal';
import ListaNotas from './components/ListaNotas.jsx';

const el = document.getElementById("sidebar-root");
if (el) {
  const path = el.dataset.path;
  const page = `/${path.split("/")[1]}`;
  console.log(page)
  const root = createRoot(el);
  root.render(<Sidebar initialPage={page} />);
}

// Variables globales para el modal de confirmación reutilizable
let eliminarModal = null;
let confirmModal = null;
let eliminarCuotasModal = null;
let currentDeleteAction = null;

// Función auxiliar para mostrar el modal de confirmación
// Exportada globalmente para que otros módulos puedan usarla
window.mostrarModalEliminar = function(titulo, mensaje, detalle, onConfirm) {
  const tituloElement = document.querySelector('#deleteModal .modal-title');
  if (tituloElement) {
    tituloElement.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${titulo}`;
  }
  
  const mensajeElements = document.querySelectorAll('#deleteModal .modal-body p');
  if (mensajeElements.length >= 1) {
    mensajeElements[0].innerHTML = mensaje;
  }
  
  const detalleElement = document.getElementById('detalleElementoModal');
  if (detalleElement) {
    detalleElement.innerHTML = detalle;
  }
  
  currentDeleteAction = onConfirm;
  
  if (eliminarModal) {
    eliminarModal.show();
  }
};

// Modal confirmación (funciona para carreras y ediciones)
// Variable para almacenar el tipo de inscripción actual
let tipoInscripcionActual = null;

window.mostrarModalConfirmar = function(nombre, alumno_id, inscripcion_id, tipo) {  

  document.getElementById('carrera-nombre-modal').textContent = nombre;
  document.getElementById('inscripcion-id-hidden').value = inscripcion_id;
  document.getElementById('alumno-id-hidden').value = alumno_id;
  tipoInscripcionActual = tipo;
  
  // Establecer fecha actual por defecto
  const fechaActual = new Date().toISOString().split('T')[0];
  document.getElementById('fecha-inscripcion-input').value = fechaActual; 
  
  if (confirmModal) {
    confirmModal.show();
  }

};

// Función global para mostrar modal con cuotas (funciona para carreras y ediciones)
window.mostrarModalEliminarConCuotas = function(nombre, cuotas, alumnoId, inscripcionId, tipo) {

    // Actualizar título
    const nombreElement = document.getElementById('modalCuotasCarreraNombre');
    if (nombreElement) {
        nombreElement.textContent = nombre;
    }
    
    // Generar lista de cuotas
    const listaCuotas = document.getElementById('listaCuotasEliminar');
    if (listaCuotas) {
        listaCuotas.innerHTML = '';
        
        let tienePagos = false;
        cuotas.forEach(cuota => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            
            let badgeClass = 'bg-secondary';
            if (cuota.estado === 'Paga') {
                badgeClass = 'bg-success';
                tienePagos = true;
            } else if (cuota.estado === 'Parcial') {
                badgeClass = 'bg-warning';
                tienePagos = true;
            }
            
            li.innerHTML = `
                Cuota ${cuota.numero}
                <span class="badge ${badgeClass}">${cuota.estado}</span>
            `;
            listaCuotas.appendChild(li);
        });
        
        // Mostrar advertencia si hay pagos
        const advertencia = document.getElementById('advertenciaPagos');
        if (advertencia) {
            if (tienePagos) {
                advertencia.classList.remove('d-none');
            } else {
                advertencia.classList.add('d-none');
            }
        }
        
        // Actualizar el texto de eliminación según el tipo
        const textoEliminacion = document.getElementById('textoEliminacion');
        if (textoEliminacion) {
            if (tipo === 'carrera') {
                textoEliminacion.innerHTML = '<i class="bi bi-info-circle me-1"></i>Al eliminar la inscripción, se eliminarán todas las <strong>cuotas</strong> asociadas y sus registros de <strong>pago</strong>.';
            } else if (tipo === 'edicion') {
                textoEliminacion.innerHTML = '<i class="bi bi-info-circle me-1"></i>Al eliminar la inscripción, se eliminarán todas las <strong>cuotas</strong> asociadas, sus registros de <strong>pago</strong> y las <strong>notas</strong> asociadas.';
            }
        }
    }
    
    // Configurar botón de confirmación
    const btnConfirmar = document.getElementById('btnConfirmarEliminacionCuotas');
    if (btnConfirmar) {
        btnConfirmar.onclick = function() {
            const form = document.createElement('form');
            form.method = 'POST';
            // Construir URL según el tipo (carrera o edicion)
            if (tipo === 'carrera') {
                form.action = `/alumno/${alumnoId}/desinscribir-carrera/${inscripcionId}`;
            } else if (tipo === 'edicion') {
                form.action = `/alumno/${alumnoId}/desinscribir-edicion/${inscripcionId}`;
            } else {
                console.error(`Tipo de inscripción inválido: ${tipo}. Debe ser 'carrera' o 'edicion'.`);
                return;
            }
            document.body.appendChild(form);
            form.submit();
        };
    }
    
    if (eliminarCuotasModal) {
        eliminarCuotasModal.show();
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  
  const eliminarModalElement = document.getElementById('deleteModal');
  if (eliminarModalElement) {
    eliminarModal = new Modal(eliminarModalElement);
    
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', () => {
      if (currentDeleteAction) {
        currentDeleteAction();
        currentDeleteAction = null;
      }
      eliminarModal.hide();
    });
  }

  const inscribirModalElement = document.getElementById('inscribirModal');
  if (inscribirModalElement) {
    confirmModal = new Modal(inscribirModalElement);
    
    document.getElementById('btn-confirmar-inscripcion')?.addEventListener('click', (e) => {
      e.preventDefault();
      // Validar formulario
      const form = document.getElementById('form-inscripcion-carrera');
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const alumnoId = document.getElementById('alumno-id-hidden').value;
      const inscripcionId = document.getElementById('inscripcion-id-hidden').value;
      const tipo = tipoInscripcionActual || 'sin definir';

      if (!inscripcionId || !alumnoId) {
        console.error("Error: No se encontró el ID de la inscripción o del alumno.");
        return;
      }

      // Crear formulario POST para inscribir con los datos del modal
      const formPost = document.createElement('form');
      formPost.method = 'POST';
      
      // Construir URL según el tipo
      if (tipo === 'carrera') {
        formPost.action = `/alumno/${alumnoId}/inscribir-carrera/${inscripcionId}`;
      } else if (tipo === 'edicion') {
        formPost.action = `/alumno/${alumnoId}/inscribir-edicion/${inscripcionId}`;
      } else {
        console.error(`Tipo de inscripción inválido: ${tipo}`);
        return;
      }

      // Agregar campos ocultos con los datos (nombres deben coincidir con parámetros del controller)
      const nroLegajoValue = document.getElementById('nro-legajo-input').value;
      if (nroLegajoValue) {
        const nroLegajoInput = document.createElement('input');
        nroLegajoInput.type = 'hidden';
        nroLegajoInput.name = 'nroLegajo';
        nroLegajoInput.value = nroLegajoValue;
        formPost.appendChild(nroLegajoInput);
      }

      const valorDescuentoInput = document.createElement('input');
      valorDescuentoInput.type = 'hidden';
      valorDescuentoInput.name = 'valorDescuento';
      valorDescuentoInput.value = document.getElementById('descuento-valor-hidden').value;
      formPost.appendChild(valorDescuentoInput);

      const descripcionDescuentoInput = document.createElement('input');
      descripcionDescuentoInput.type = 'hidden';
      descripcionDescuentoInput.name = 'descripcionDescuento';
      descripcionDescuentoInput.value = document.getElementById('descripcion-descuento-hidden').value || '';
      formPost.appendChild(descripcionDescuentoInput);

      const fechaInput = document.createElement('input');
      fechaInput.type = 'hidden';
      fechaInput.name = 'fecha_inscripcion';
      fechaInput.value = document.getElementById('fecha-inscripcion-input').value;
      formPost.appendChild(fechaInput);

      document.body.appendChild(formPost);
      formPost.submit();
      confirmModal.hide();
    });
    
  }

  const EliminacionCuotasElement = document.getElementById('modalEliminacionCuotas');
  if (EliminacionCuotasElement) {
    eliminarCuotasModal = new Modal(EliminacionCuotasElement);
  }

  // Renderizar lista de notas (funciona tanto para alumno como para edición)
  const listaNotas = document.getElementById("lista-notas");
  if (listaNotas && !listaNotas.hasChildNodes()) {  // Solo renderizar si no tiene hijos (evitar doble renderizado)
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
          
          mostrarModalEliminar(
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
    root.render(<ListaNotas labels={labels} attributes={attributes} rows={rows} opcionesAcciones={opcionesAcciones} />);
  }
});


