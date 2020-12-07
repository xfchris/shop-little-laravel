import React from "react"
import styled from "styled-components"

const SassProducto = styled.div`
  margin: 2px auto;
  overflow: hidden;
  flex-wrap: nowrap;
  img {
    margin-right: 10px;
    border-radius: 4px;
  }
  div{
      #descripcion{
          width: 43%;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
      }
  }
`;

export default function OrderInfoProducto({producto}) {
    return (
        <SassProducto className="row mb-4">
            <img src={producto.imagen} />
            <div>
                <b>Producto: {producto.nombre}</b>
                <p title={producto.descripcion} id="descripcion">
                    {producto.descripcion}
                </p>
                <b>Precio: $ {producto.precio.toLocaleString()} ({producto.moneda})</b>
            </div>

        </SassProducto>
    )
}
