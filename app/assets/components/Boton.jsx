export default function Boton({ 
    children,           // Texto del botón
    onClick,            // Función que se ejecuta al hacer click
    variant, // principal, secundario, peligro
    disabled = false,   // Si está deshabilitado
    type = 'button',    // Tipo HTML: button, submit, reset
    className = '',     // Clases CSS adicionales
    ...props            // Otros props
}) {
    // Clases base del botón (sin Bootstrap)
    const baseClasses = 'boton-base';

    // Clases según el variant (solo custom)
    const variantClasses = {
        principal: 'btn-amarillo',
        secundario: 'btn-verde',
        peligro: 'btn-rojo'
    };
    // Determinar clases finales
    const buttonClasses = [
        baseClasses,
        variantClasses[variant] || variantClasses.principal,
        className
    ].join(' ');
    // Estilos inline para bordes redondeados
    const buttonStyles = {
        borderRadius: '10px',
        transition: 'all 0.2s ease-in-out',
        fontWeight: '500',
        ...props.style // Permitir estilos adicionales
    };
    return (
        <button
            type={type}
            className={buttonClasses}
            style={buttonStyles}
            onClick={onClick}
            disabled={disabled}
            {...props}
        >
            {children}
        </button>
    );
}