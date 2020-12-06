import React from 'react'
import Swal from 'sweetalert2'
import {buscarOrden} from "../api/Orden";

export default function OrderForm({formData, setFormData, setVista, setInfoPago}) {

    const onChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        })
    }

    const onSubmit = async (e) => {
        e.target.checkValidity();
        e.preventDefault();

        try{
            let res =  await buscarOrden(formData)
            respuestaConsultaOrden(res.data?.data, setVista, setInfoPago)
        }catch(error) {
            let msg = error?.response?.data?.msg
            if (msg){
                Swal.fire(msg)
            }
        }
    }

    return (
        <>
            <form method="POST" onSubmit={onSubmit} onChange={onChange}>
                <fieldset>
                    <legend>Nueva orden de pago</legend>

                    <div className="form-group">
                        <label htmlFor="customer_name">Nombres y apellidos</label>
                        <input type="text"
                               className="form-control"
                               name="customer_name"
                               maxLength='80'
                               defaultValue={formData.customer_name}
                               placeholder="Ejemplo: Juan Perez" required/>
                    </div>

                    <div className="form-group">
                        <label htmlFor="customer_email">Correo electrónico</label>
                        <input type="email"
                               className="form-control"
                               name="customer_email"
                               maxLength='120'
                               defaultValue={formData.customer_email}
                               placeholder="Ejemplo: juan23perez@gmail.com" required/>
                    </div>

                    <div className="form-group">
                        <label htmlFor="customer_mobile">Número telefónico</label>
                        <input type="text"
                               className="form-control"
                               name="customer_mobile"
                               maxLength='40'
                               defaultValue={formData.customer_mobile}
                               placeholder="Escribe aquí tu numero de teléfono o celular" required/>
                    </div>

                    <button type="submit" className="btn btn-primary">Continuar</button>
                </fieldset>
            </form>
        </>
    )
}

/**
 * Si no existe una orden creada con datos similares, muestra estado para continuar con el pago
 * @param datos
 * @param setVista
 * @param setInfoPago
 */
function respuestaConsultaOrden(datos, setVista, setInfoPago){
    if ((datos)) {
        Swal.fire({
            title: 'Ya existe un pago en proceso con los datos ingresados',
            html:'¿Que desea hacer?',
            showCancelButton: true,
            confirmButtonText: `Ver estado del pago`,
            cancelButtonText: `Regresar`,
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                setInfoPago(datos)
                setVista('OrderEstado')
            }
        })
    } else {
        setVista('OrderPreview')
    }
}
