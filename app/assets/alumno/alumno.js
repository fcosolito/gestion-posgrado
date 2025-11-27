import { Modal } from 'bootstrap';
import Lista from "../components/Lista.jsx";
import ListaCuotas from "../components/ListaCuotas.jsx";
import ListaInscripciones from "../components/ListaInscripciones.jsx";
import { createRoot } from "react-dom/client";
import '../styles/app.css';
import 'bootstrap/dist/js/bootstrap.min.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import ListaNotas from "../components/ListaNotas.jsx";

document.addEventListener("DOMContentLoaded", () => {
  const listaDiv = document.getElementById("lista-alumnos");
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
          
          window.mostrarModalEliminar(
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
    // Detectar si es carrera o edición por la presencia del atributo 'curso' en la tabla
    const tipo = attributes.includes('curso') ? 'edicion' : 'carrera';
    
    const handleAccionClick = (row) => {
      if (row.accion === 'Inscribir') {

        const nombre = row.nombre;
        const id= row.id;

        // Abrir modal de confirmación de inscripción
        window.mostrarModalConfirmar(nombre, alumnoId, id, tipo)

      } else if (row.accion === 'Borrar') {
        // Verificar si tiene cuotas
        if (row.cuotas && row.cuotas.length > 0) {
          // Mostrar modal con detalle de cuotas (pasando el tipo)
          window.mostrarModalEliminarConCuotas(
            row.nombre,
            row.cuotas,
            alumnoId,
            row.id,
            tipo
          );
        } else {
          // Mostrar modal de confirmación simple si no hay cuotas
          window.mostrarModalEliminar(
            'Confirmar eliminación',
            `¿Está seguro de que desea eliminar la inscripción a ${row.nombre}? También se eliminarán las <strong>notas</strong> asociadas.`,
            `No hay cuotas asociadas a esta inscripción.`,
            () => {
              // Crear y enviar formulario POST para eliminación
              const form = document.createElement('form');
              form.method = 'POST';
              if (tipo === 'carrera') {
                form.action = `/alumno/${alumnoId}/desinscribir-carrera/${row.id}`;
              } else if (tipo === 'edicion') {
                form.action = `/alumno/${alumnoId}/desinscribir-edicion/${row.id}`;
              }
              document.body.appendChild(form);
              form.submit();
            }
          );
        }
      }
    };
    
    const root = createRoot(listaInscripciones);
    root.render(<ListaInscripciones labels={labels} attributes={attributes} rows={rows} onAccionClick={handleAccionClick} />);
  }

   const listaNotasAlumno = document.getElementById("lista-notas");
  if (listaNotasAlumno) {
    const labels = JSON.parse(listaNotasAlumno.dataset.labels || '[]');
    const attributes = JSON.parse(listaNotasAlumno.dataset.attributes || '[]');
    const rows = JSON.parse(listaNotasAlumno.dataset.rows || '[]');
    
    const opcionesAcciones = [
      {
        label: 'Editar (a implementar)',
        onClick: (rowIndex) => {
          //const alumnoId = rows[rowIndex].id;
          //window.location.href = `/alumno/${alumnoId}/visualizar`;
        }
      },
      {
        label: 'Eliminar',
        onClick: (rowIndex) => {
          const nota = rows[rowIndex];
          const datosNota = `${nota.nota} ${nota.descripcion || 'Sin descripción'} cargada el ${nota.fecha_carga}`;
          
          window.mostrarModalEliminar(
            'Confirmar eliminación',
            '¿Está seguro de que desea eliminar la nota y su documentación asociada?',
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
    
    
    const root = createRoot(listaNotasAlumno);
    root.render(<ListaNotas labels={labels} attributes={attributes} rows={rows} opcionesAcciones={opcionesAcciones} />);
  }

  //TODO revisar esto es para la seleccion de descuento a la hora de inscribir a carrera
  const descuentoSelect = document.getElementById('descuento-select');
  const descuentoValorHidden = document.getElementById('descuento-valor-hidden');
  const descripcionDescuentoHidden = document.getElementById('descripcion-descuento-hidden');
  const infoDescuento = document.getElementById('info-descuento');
  const valorTexto = document.getElementById('valor-texto');
  const descripcionTexto = document.getElementById('descripcion-texto');
  
  if (descuentoSelect) {
    descuentoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const valor = selectedOption.getAttribute('data-valor');
            const descripcion = selectedOption.getAttribute('data-descripcion');
            
            // Actualizar campos ocultos
            descuentoValorHidden.value = valor;
            descripcionDescuentoHidden.value = descripcion;
           
            // Mostrar información
            valorTexto.textContent = valor;
            descripcionTexto.textContent = descripcion;
            infoDescuento.style.display = 'block';
        } else {
            // Resetear valores
            descuentoValorHidden.value = '0';
            descripcionDescuentoHidden.value = '';
            infoDescuento.style.display = 'none';
        }
    });
  }

});