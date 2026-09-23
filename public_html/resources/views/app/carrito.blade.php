@extends('layouts.app')

@section('titulo', 'Carrito | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: carrito.')

@section('contenido')
    <div class="contenedor">
      <div class="panel-titulo">
        <h1>Carrito de compras</h1>
        <p>Revisa las cantidades y confirma el pedido.</p>
      </div>

      @include('components.aviso')

      <div class="bloque" style="margin-bottom:22px;">
        <div class="tabla-scroll">
          <table class="tabla-app" id="tabla-carrito">
            <caption class="oculto-visual">L&iacute;neas del carrito de compras</caption>
            <thead>
              <tr>
                <th scope="col">Producto</th>
                <th scope="col" data-rn="RN-11" data-rn-nota="Precio histórico">Precio unitario</th>
                <th scope="col" data-rn="RN-10" data-rn-nota="Cantidad &gt; 0">Cantidad</th>
                <th scope="col">Subtotal</th>
                <th scope="col"><span class="oculto-visual">Acciones</span></th>
              </tr>
            </thead>
            <tbody id="cuerpo-carrito">
              @forelse ($lineas as $linea)
                @php
                    $productoId = (int) data_get($linea, 'producto_id');
                    $precio = (float) data_get($linea, 'precio_unitario');
                    $cantidad = (int) data_get($linea, 'cantidad');
                @endphp
                <tr>
                  <td data-label="Producto">{{ data_get($linea, 'nombre') }}</td>
                  <td data-label="Precio unitario">S/ {{ number_format($precio, 2) }}</td>
                  <td data-label="Cantidad">
                    <form method="POST" action="{{ route('carrito.actualizar', $productoId) }}" style="display:flex;gap:8px;align-items:center;">
                      @csrf
                      @method('PATCH')
                      <label class="oculto-visual" for="cantidad-{{ $productoId }}">Cantidad de {{ data_get($linea, 'nombre') }}</label>
                      <input type="number" id="cantidad-{{ $productoId }}" name="cantidad" min="1" step="1" value="{{ $cantidad }}"
                             style="width:76px;padding:7px 9px;border:1px solid var(--borde);border-radius:8px;font:inherit;">
                      <button type="submit" class="boton-mini">Actualizar</button>
                    </form>
                  </td>
                  <td data-label="Subtotal">S/ {{ number_format((float) data_get($linea, 'subtotal', $precio * $cantidad), 2) }}</td>
                  <td>
                    <form method="POST" action="{{ route('carrito.quitar', $productoId) }}">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="boton-mini">Quitar</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5">El carrito est&aacute; vac&iacute;o. <a href="{{ route('catalogo') }}">Ir al cat&aacute;logo</a>.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <p class="nota-regla">El subtotal se recalcula siempre como precio &times; cantidad;
           nunca se guarda un valor desactualizado.</p>
      </div>

      <div class="rejilla rejilla-2">
        <div class="bloque">
          <h2>Total del pedido</h2>
          @php
              /* Desglose fiscal peruano: los precios al publico incluyen IGV 18%. */
              $igvTotal = round((float) $total - ((float) $total / 1.18), 2);
              $baseTotal = round((float) $total - $igvTotal, 2);
          @endphp
          <div class="total-desglose" id="total-carrito" style="margin-top:10px;">
            <p class="total-fila"><span>Subtotal (sin IGV)</span><span>S/ {{ number_format($baseTotal, 2) }}</span></p>
            <p class="total-fila"><span>IGV 18%</span><span>S/ {{ number_format($igvTotal, 2) }}</span></p>
            <p class="total-fila total-fila--final"><span>Total a pagar</span><span>S/ {{ number_format((float) $total, 2) }}</span></p>
          </div>
          <p class="fiscal-nota">Precios con IGV incluido (D.S. 055-99-EF). Al pagar recibir&aacute;s tu comprobante por correo.</p>
          <form method="POST" action="{{ route('carrito.confirmar') }}">
            @csrf
            <p class="nota-regla" style="margin-top:16px;" data-rn="RN-14" data-rn-nota="Origen del pedido">
              Esto es una <strong>compra directa</strong>. Los despachos de
              suscripci&oacute;n los emite el sistema solo, al vencer el plan.
            </p>
            <p style="margin-top:16px;" data-rn="RN-12" data-rn-nota="Sin stock no hay pedido">
              <button type="submit" class="boton" id="btn-confirmar">Confirmar pedido</button>
            </p>
          </form>
        </div>
        <div class="bloque">
          <h2>Qu&eacute; se comprueba al confirmar</h2>
          <ul class="lista-simple">
            <li>Que cada l&iacute;nea tenga stock suficiente, antes de tocar el inventario.</li>
            <li>Que ninguna l&iacute;nea deje el stock en negativo.</li>
            <li>Si una sola l&iacute;nea falla, no se registra ninguna parte del pedido.</li>
            <li>Al descontar, avisa qu&eacute; productos quedaron bajo el punto de reorden.</li>
          </ul>
        </div>
      </div>
    </div>
@endsection
