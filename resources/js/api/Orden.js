import axios from "axios";

export function buscarOrden(data){
    try{
        return axios.post('/json/buscar-orden', data)
    }catch(error) {
        console.log("error", error);
    }
}

export function realizarPago(data){
    try{
        return axios.post('/json/iniciar-pago', data)
    }catch(error) {
        console.log("error", error);
    }
}
