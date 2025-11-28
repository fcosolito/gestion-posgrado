import { useState, useEffect } from "react";

// placeholder: lo que se muestra cuando el input esta vacio
// setItem: la funcion que setea el item seleccionado en el padre
// las funciones get son las que definen que informacion se muestra de los items
// y que id se usa
export default function SelectorDropdown({ items, placeholder, item, setItem, getId, getLabel, getDetalle }) {
  const [query, setQuery] = useState("");
  const [isOpen, setIsOpen] = useState(false);
  const [isSelected, setIsSelected] = useState(false);
  const [resultados, setResultados] = useState([]);

  useEffect(() => {
    if (item) {
      setQuery(getLabel(item));
      setIsSelected(true);
    }
  }, []);

  useEffect(() => {
    if (!item) {
      setQuery("");
      setIsSelected(false);
      setIsOpen(false);
    }
  }, [item]);

  useEffect(() => {
    setResultados(items
      .filter((item) => 
        getLabel(item).toLowerCase().includes(query.toLowerCase())
      )
      .map((item) => ({
        id: getId(item),
        label: getLabel(item),
        detalle: getDetalle(item),
        })
      )
    );

  }, [items])
  

  const handleChange = async (e) => {
    setItem(null);
    setIsSelected(false);

    const value = e.target.value;
    setQuery(value);
    setIsOpen(value.length > 0); 
  };

  const handleSelect = (item) => {
    setItem(item);
    setIsSelected(true);
    setQuery(item.label);
    setIsOpen(false);
  };

  return (
    <div className="position-relative" >
      <input
        type="text"
        className={`form-control ${isSelected ? "seleccion-buscador" : ""}`}
        placeholder={placeholder}
        disabled={!(items.length > 0)}
        value={query}
        onChange={handleChange}
        onBlur={() => setTimeout(() => setIsOpen(false), 100)} 
        onFocus={() => query && setIsOpen(true)}
      />

      {isOpen && (
        <ul className="dropdown-menu show w-100" style={{ zIndex: 1050,}}>

        {resultados.length > 0 && 
            resultados.map((item) => (
                <li key={item.id}>
                    <button
                        className="dropdown-item"
                        onMouseDown={() => handleSelect(item)}
                    >
                        <div>
                        <strong>{item.label}</strong>
                        </div>
                        <div className="fs-6">
                        {item.detalle}
                        </div>
                    </button>
                </li>
            ))}

        {resultados.length === 0 && (
            <li className="dropdown-item text-muted">Sin resultados</li>
        )}
        </ul>
      )}
    </div>
  );
}