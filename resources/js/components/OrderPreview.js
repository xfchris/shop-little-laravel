import React from 'react'
import {buscarOrden, realizarPago} from "../api/Orden";
import Swal from "sweetalert2";

export default function OrderPreview({formData, setVista, setInfoPago}) {

    const modificiarData = () => {
        setVista('OrderForm')
    }

    const pagar = async () => {
        try {
            //inicio el pago y obtiene url de redireccion
            let res = await realizarPago(formData)
            if (res.data.msg){
                //muestra estado de solicitud
                setInfoPago({
                    'status':'Creada',
                    'url':res.data.msg
                })
                setVista('OrderEstado')
                //redirecciona a placetopay
                abrirLink(res.data.msg, '_blank')
            }
        } catch (error) {
            let msg = error?.response?.data?.msg
            if (msg){
                Swal.fire(msg)
            }
        }
    }
    return (
        <>
            <legend>Resumen de pago</legend>

            <div className="mb-2 row">
                <div className="col-sm-3">Nombre completo:</div>
                <div className="col-sm-9 ml-0">{formData.nombres}</div>
            </div>
            <div className="mb-2 row">
                <div className="col-sm-3">Email:</div>
                <div className="col-sm-9 ml-0">{formData.email}</div>
            </div>
            <div className="mb-2 row">
                <div className="col-sm-3">Teléfono:</div>
                <div className="col-sm-9 ml-0">{formData.telefono}</div>
            </div>

            <div className="mt-4">
                <button onClick={pagar} className="btn btn-success mr-3">Realizar pago</button>
                <button onClick={modificiarData} className="btn btn-info">Modificar información</button>
            </div>
        </>
    )
}

function abrirLink(url, target){
    const link = document.querySelector('#linkOculto')
    link.href = url
    link.click()
}

