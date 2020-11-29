import React from 'react'

export default function Subcomp2({formData, setVistaForm}){
    const mostrarForm = () => {
        setVistaForm(true);
    }
    return(
        <>
            <div>El componente 2: {formData.nombre}</div>
            <button onClick={mostrarForm} />
        </>
    )
}
