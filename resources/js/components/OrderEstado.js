import React from 'react'

export default function OrderEstado({setVista, infoPago}) {
    const mostrarForm = () => {
        setVista('OrderForm')
    }

    return (
        <>
            <div className="mb-4">
                <legend>Estado de su orden</legend>
                Su orden se encuentra en estado: <b>{infoPago?.status}</b>
            </div>
            <button onClick={mostrarForm} className='btn btn-info'>Reintentar pago</button>
        </>
    )
}
