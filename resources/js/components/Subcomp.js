import React from 'react';
import Swal from 'sweetalert2'

export default function Subcomp({variable, formData, setFormData, setVistaForm}){

    const mostrarAlert = () => {
        axios.get('https://pokeapi.co/api/v2/pokemon/ditto')
            .then(function(x){
                Swal.fire(JSON.stringify(x.data.sprites.back_default))
            })
            .catch((x)=>{
                Swal.fire("error")
            })
        //Swal.fire('Hello world! react '+formData.nombre)
    }

    const onChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        })
    }

    const mostrarComp2 = () => {
        setVistaForm(false);
    }


    return (
        <>
            <div>Token: {getToken()}</div>
            <div>nombre: {formData.nombre}</div>
            <input
                type='text'
                name='nombre'
                defaultValue={formData.nombre}
                onChange={onChange}
                className='form-control' />
            <button onClick={mostrarAlert}>dame click</button>
            <button onClick={mostrarComp2}>Componente 2</button>
        </>
    )
}

function getToken(){
    return document.querySelector('meta[name="csrf-token"]').content
}

