import { useState, useRef, useEffect } from 'react';

export default function DropdownAcciones({ rowId, opciones }) {
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef(null);

    // Cerrar dropdown al hacer click fuera
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };

        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [isOpen]);

    const toggleDropdown = () => {
        setIsOpen(!isOpen);
    };

    const handleOptionClick = (onClickHandler) => {
        onClickHandler(rowId);
        setIsOpen(false);
    };

    return (
        <div className="dropdown-acciones" ref={dropdownRef}>
            <button 
                className="btn-acciones" 
                onClick={toggleDropdown}
                type="button"
            >
                <span className="dot"></span>
                <span className="dot"></span>
                <span className="dot"></span>
            </button>

            {isOpen && (
                <div className="dropdown-menu-acciones">
                    {opciones.map((opcion, idx) => (
                        <button
                            key={idx}
                            className="dropdown-item-acciones"
                            onClick={() => handleOptionClick(opcion.onClick)}
                            type="button"
                        >
                            {opcion.label}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
