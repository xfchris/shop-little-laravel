import React, {useState} from 'react';
import ReactDOM from 'react-dom';
import OrderForm from "./OrderForm";
import OrderInfoProducto from "./OrderInfoProducto";
import OrderPreview from "./OrderPreview";
import OrderEstado from "./OrderEstado";

export default function OrderPanel() {

    const producto = getProducto();
    const [infoPago, setInfoPago] = useState({})
    const [formData, setFormData] = useState(initialFDState)
    const [vista, setVista] = useState('OrderForm')


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

/**
 * Retorna un objeto inicial para el usuario
 *
 * @returns {{customer_mobile: string, customer_email: string, customer_name: string}}
 */
function initialFDState() {
    return {
        customer_name: "Juan Perez",
        customer_email: "juan_perez@hotmail.com",
        customer_mobile: "3334445999"
    }
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
