import React, {useState} from 'react'
import {realizarPago} from "../api/Orden";
import Swal from "sweetalert2";

export default function OrderPreview({formData, setVista, setInfoPago}) {

    const [btns, setBtns] = useState({name: 'Realizar pago', disabled: ''})

    const modificiarData = () => {
        setVista('OrderForm')
    }

    const pagar = async () => {
        try {
            setBtns({name: 'Espere...', disabled: 'disabled'})
            //inicio el pago y obtiene url de redireccion
            let res = await realizarPago(formData)
            setBtns({name: 'Realizar pago', disabled: ''})
            if (res.data?.data?.url) {
                //muestra estado de solicitud
                setInfoPago({
                    'status': 'Creada',
                    'url': res.data.data.url
                })
                setVista('OrderEstado')
                //redirecciona a placetopay
                abrirLink(res.data.data.url, '_blank')
            }
        } catch (error) {
            setBtns({name: 'Realizar pago', disabled: 'disabled'})
            let msg = error?.response?.data?.errors?.msg
            if (msg) {
                Swal.fire(msg)
            }
        }
    }
    return (
        <>
            <legend>Resumen de pago</legend>

            <div className="mb-2 row">
                <div className="col-sm-3">Nombre completo:</div>
                <div className="col-sm-9 ml-0">{formData.customer_name}</div>
            </div>
            <div className="mb-2 row">
                <div className="col-sm-3">Email:</div>
                <div className="col-sm-9 ml-0">{formData.customer_email}</div>
            </div>
            <div className="mb-2 row">
                <div className="col-sm-3">Teléfono:</div>
                <div className="col-sm-9 ml-0">{formData.customer_mobile}</div>
            </div>

            <div className="mt-4">
                <button onClick={pagar} className="btn btn-success mr-3" disabled={btns.disabled}>{btns.name}</button>
                <button onClick={modificiarData} className="btn btn-info">Modificar información</button>
            </div>
        </>
    )
}

/**
 * Permite abrir en otra pestaña evitando que el navegador bloquee
 *
 * @param url
 * @param target
 */
function abrirLink(url, target) {
    const link = document.querySelector('#linkOculto')
    link.href = url
    link.click()
}

