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
            console.log(res)
            //redirecciona a placetopay
            //muestra estado de solicitud
            setInfoPago(res.data)
            setVista('OrderEstado')

        } catch (error) {
            Swal.fire("Se presentó un error desconocido.")
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
