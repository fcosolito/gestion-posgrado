import { useState, useEffect } from "react";

// fetchItems: la funcion que trae los items en formato [{ id: 0, label: "label" },]
// placeholder: lo que se muestra cuando el input esta vacio
// setItem: la funcion que setea el item seleccionado en el padre
// las funciones get son las que definen que informacion se muestra de los items
// y que id se usa
export default function BuscadorDropdown({ fetchItems, placeholder, item, setItem, getId, getLabel, getDetalle }) {
  const [query, setQuery] = useState("");
  const [isOpen, setIsOpen] = useState(false);
  const [isSelected, setIsSelected] = useState(false);
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (item) {
      setQuery(getLabel(item));
      setIsSelected(true);
    }
  }, []);

  useEffect(() => {
    setLoading(true);

    if (query.trim() === "") {
        setItems([]);
        setIsOpen(false);
        return
    }

    const fetchData = async () => {
        try {
            const res = await fetchItems(query);
            const data = await res.json();
            const formatedItems = data.map((item) => {
                return {
                    id: getId(item),
                    label: getLabel(item),
                    detalle: getDetalle(item),
                }
            });
            setItems(formatedItems);
        } catch (err) {
            alert(err.message);
        } finally {
            setLoading(false);
        }
    }

    fetchData();

  }, [query]);
  

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
        value={query}
        onChange={handleChange}
        onBlur={() => setTimeout(() => setIsOpen(false), 100)} // cerramos tras click
        onFocus={() => query && setIsOpen(true)}
      />

      {isOpen && (
        <ul className="dropdown-menu show w-100" style={{ zIndex: 1050,}}>
        {loading && (
            <li className="dropdown-item text-muted">
                Cargando...
            </li>
        )}

        {!loading && items.length > 0 && 
            items.map((item) => (
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

        {!loading && items.length === 0 && (
            <li className="dropdown-item text-muted">Sin resultados</li>
        )}
        </ul>
      )}
    </div>
  );
}