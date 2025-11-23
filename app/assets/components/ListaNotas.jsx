import DropdownAcciones from './DropdownAcciones';

export default function ListaNotas({ labels, attributes, rows, opcionesAcciones = [] }) {

  // Función para renderizar el contenido de la celda
  const renderCellContent = (attribute, value) => {
    // Si el valor contiene HTML, renderízalo sin escapar
    if (typeof value === 'string' && 
        (value.includes('<a') || value.includes('<span') || value.includes('<div') || value.includes('📎'))) {
      return <span dangerouslySetInnerHTML={{ __html: value }} />;
    }
    // Para las demás columnas, comportamiento normal
    return value;
  };

  return (
    <div className="lista-generica">
        <div className="lista-header">
        {labels.map(label => (
          <span key={label} className="lista-label">{label}</span>
        ))}
        {opcionesAcciones.length > 0 && (
          <span className="lista-label">Acciones</span>
        )}
      </div>
      <div className="lista-body">
        {rows.map((row, idx) => (
          <div className="lista-row" key={idx}>
            {attributes.map(attr => (
              <span key={attr} className="lista-cell">
                {renderCellContent(attr, row[attr])}
              </span>
            ))}
            {opcionesAcciones.length > 0 && (
              <span className="lista-cell">
                <DropdownAcciones rowId={idx} opciones={opcionesAcciones} />
              </span>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}