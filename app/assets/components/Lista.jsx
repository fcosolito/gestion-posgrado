import DropdownAcciones from './DropdownAcciones';

export default function ListaGenerica({ labels, attributes, rows, opcionesAcciones = [] }) {

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
            {attributes.map(attr => (
              <span key={attr} className="lista-cell">{row[attr]}</span>
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