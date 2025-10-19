import {useEffect, useState} from 'react';
import Actions from './Actions';

export default function Listado({ dataUrl, onSave, onDelete, columns, keyField, actions }) {

    const [data, setData] = useState([]);
    const [editingRow, setEditingRow] = useState(null);
    const [editValues, setEditValues] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const handleEdit = (item) => {
        setEditingRow(item[keyField]);
        setEditValues(item);
    };

    const handleCancel = () => {
        setEditingRow(null);
        setEditValues({});
    };

    const handleChange = (field, value) => {
        setEditValues((prev) => ({ ...prev, [field]: value }));
    };

    const handleSave = () => {
        if (onSave) onSave(editValues);
        setData((prev) =>
        prev.map((item) => (item[keyField] === editValues[keyField] ? editValues : item))
        );
        setEditingRow(null);
    };

    const all_actions = [...actions, 
        {
            label: "Editar",
            onClick: (item) => handleEdit(item),
        },
        {
            label: "Eliminar",
            onClick: (item) => onDelete(item),
        }
    ];

    useEffect(() => {
        fetch(dataUrl)
        .then((res) => {
            if (!res.ok) throw new Error(`Error HTTP ${res.status}`);
            return res.json();
        })
        .then((json) => {
            setData(json);
            setLoading(false);
        })
        .catch((err) => {
            setError(err.message);
            setLoading(false);
        });
    }, [dataUrl]);

    if (loading) return <div className="p-4 text-gray-500">Cargando...</div>;
    if (error) return <div className="p-4 text-red-500">Error: {error}</div>;
    if (!data || data.length === 0) {
        return <div className="text-gray-500 p-4">No hay datos para mostrar</div>;
    }

    return (
        <table className="min-w-full border border-gray-300 rounded-md">
        <thead className="bg-gray-100">
            <tr>
            {columns.map((col) => (
                <th key={col.key} className="text-left p-2 border-b border-gray-300">
                {col.label}
                </th>
            ))}
            <th className="p-2 border-b border-gray-300">Acciones</th>
            </tr>
        </thead>
        <tbody>
            {data.map((item) => {
                const isEditing = editingRow === item[keyField];
                return (
                    <tr key={item[keyField]} className="hover:bg-gray-50">
                        {columns.map((col) => (
                        <td key={col.key} className="p-2 border-b border-gray-200">
                            {isEditing ? (
                                <input
                                    type="text"
                                    value={editValues[col.key] ?? ""}
                                    onChange={(e) => handleChange(col.key, e.target.value)}
                                    className="border rounded p-1 w-full"
                                />
                            ) : col.render ? (
                                col.render(item)
                            ) : (
                                item[col.key]
                            )}
                        </td>
                        ))}
                        <td>
                            {isEditing ? (
                                <div className="flex gap-2">
                                    <button
                                        onClick={handleSave}
                                        className="px-2 py-1 text-sm rounded bg-green-500 text-white hover:bg-green-600"
                                    >
                                        Guardar
                                    </button>
                                    <button
                                        onClick={handleCancel}
                                        className="px-2 py-1 text-sm rounded bg-gray-400 text-white hover:bg-gray-500"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            ) : (
                                <Actions 
                                    actions={all_actions}
                                    item={item}
                                />
                            )}
                        </td>
                    </tr>
                )
            })}
        </tbody>
        </table>
    );
}