export default function ListaInscripciones({ labels, attributes, rows, onAccionClick }) {

  const handleAccionClick = (row) => {
    if (onAccionClick) {
      onAccionClick(row);
    }
  };

  const getButtonClass = (accion) => {
    if (accion === 'Inscribir') {
      return 'btn-secundario';
    } else if (accion === 'Borrar') {
      return 'btn-peligro';
    }
    return 'btn-primario';
  };

  return (
    <div className="lista-generica">
      <div className="lista-header">
        {labels.map(label => (
          <span key={label} className="lista-label">{label}</span>
        ))}
      </div>
      <div className="lista-body">
        {rows.map((row, idx) => (
          <div className="lista-row" key={idx}>
            {attributes.map(attr => {
              // Si el atributo es 'accion', renderizar un botón en lugar de texto
              if (attr === 'accion') {
                return (
                  <span key={attr} className="lista-cell">
                    <button 
                      className={getButtonClass(row[attr])}
                      onClick={() => handleAccionClick(row)}
                      style={{ padding: '0.5rem 1rem', fontSize: '0.875rem' }}
                    >
                      {row[attr]}
                    </button>
                  </span>
                );
              }
              // Para otros atributos, mostrar texto normal
              return (
                <span key={attr} className="lista-cell">{row[attr]}</span>
              );
            })}
          </div>
        ))}
      </div>
    </div>
  );
}
