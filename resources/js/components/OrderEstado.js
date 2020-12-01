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
            {(infoPago?.url) ?
                <p>
                    Si no se abrió la nueva pestaña, haz click abajo para continuar el pago <br/>
                    <a href={infoPago.url} target='_blank' className='btn btn-info'>Continuar pago</a>
                </p>
                :
                (
                    (infoPago?.status && infoPago.status == 'REJECTED') ?
                        <button onClick={mostrarForm} className='btn btn-info'>Reintentar pago</button>
                        :
                        <p></p>

                )
            }
        </>
    )
}
