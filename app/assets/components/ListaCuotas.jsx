export default function ListaCuotas({ cuotas }) {
  
  // Función para determinar el color del estado
  const getEstadoColor = (estado) => {
    switch (estado.toLowerCase()) {
      case 'pendiente':
        return '#dc3545'; // Rojo
      case 'paga':
        return '#28a745'; // Verde
      case 'faltante':
        return '#fd7e14'; // Naranja
      default:
        return '#6c757d'; // Gris por defecto
    }
  };

  return (
    <div className="lista-cuotas">
      <div className="lista-cuotas-body">
        {cuotas && cuotas.length > 0 ? (
          cuotas.map((cuota, idx) => (
            <div className="cuota-row" key={idx}>
              <div className="cuota-nombre">
                {cuota.carreraCurso}
              </div>
              <div className="cuota-divider"></div>
              <div 
                className="cuota-estado"
                style={{ color: getEstadoColor(cuota.estado) }}
              >
                {cuota.estado}
              </div>
            </div>
          ))
        ) : (
          <div className="cuota-row-empty">
            No hay cuotas registradas
          </div>
        )}
      </div>
    </div>
  );
}
