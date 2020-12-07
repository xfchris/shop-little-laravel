import React, {useState} from 'react';
import ReactDOM from 'react-dom';
import OrderForm from "./OrderForm";
import OrderInfoProducto from "./OrderInfoProducto";
import OrderPreview from "./OrderPreview";
import OrderEstado from "./OrderEstado";

export default function OrderPanel() {

    const producto = getProducto();
    const [infoPago, setInfoPago] = useState(initialInfoPago)
    const [formData, setFormData] = useState(initialFDState)
    const [vista, setVista] = useState(initialVista)


    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-8">
                    <div className="card">
                        <div className="card-header">Compra de producto X</div>

                        <div className="card-body">
                            <OrderInfoProducto producto={producto} />
                            {
                                {
                                    'OrderForm': <OrderForm formData={formData} setFormData={setFormData} setVista={setVista} setInfoPago={setInfoPago} />,
                                    'OrderPreview': <OrderPreview formData={formData} setVista={setVista} setInfoPago={setInfoPago}/>,
                                    'OrderEstado': <OrderEstado formData={formData} setVista={setVista} infoPago={infoPago} />
                                }[vista]
                            }
                        </div>

                    </div>
                </div>
            </div>
        </div>
    );
}

function initialInfoPago(){
    let orden = document.querySelector('#orderPanel').getAttribute('data-orden')
    if (orden){
        return JSON.parse(orden)
    }
    return {}
}

/**
 * Retorna un objeto inicial para el usuario
 */
function initialFDState() {
    let orden = document.querySelector('#orderPanel').getAttribute('data-orden')
    if (orden){
        return JSON.parse(orden)
    }
    return {}
}

/**
 * Retorna el panel inicial
 */
function initialVista(){
    return document.querySelector('#orderPanel').getAttribute('data-vista')
}

/**
 * Obtengo el producto desde el backend
 */
function getProducto(){
    return JSON.parse(document.querySelector('#dataBase').getAttribute('data-producto'))
}

if (document.getElementById('orderPanel')) {
    ReactDOM.render(<OrderPanel />, document.getElementById('orderPanel'));
}
