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
            {
                (infoPago?.status && infoPago.status === 'Rechazado') ?
                    <button onClick={mostrarForm} className='btn btn-info'>Reintentar pago</button>
                    :
                    (
                        (infoPago?.url && infoPago.status !== 'Pagado') ?
                            <p>
                                Si no se abrió la nueva pestaña, haz click abajo para continuar el pago <br/>
                                <a href={infoPago.url} target='_blank' className='btn btn-info'>Continuar pago</a>
                            </p>
                            :
                            <p> </p>

                    )
            }
        </>
    )
}
