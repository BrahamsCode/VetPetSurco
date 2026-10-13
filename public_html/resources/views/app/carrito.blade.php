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
                  <td data-label="Producto">
                    <span class="linea-producto">
                      @if (isset($imagenes[$productoId]))
                        <img class="linea-miniatura" src="{{ $imagenes[$productoId] }}" alt="" width="56" height="42" loading="lazy">
                      @endif
                      <span>{{ data_get($linea, 'nombre') }}</span>
                    </span>
                  </td>
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
              $productosCentimos = \App\Services\CarritoService::aCentimos($total);
              $modalidadElegida = old('modalidad_entrega', 'RECOJO');
              $envioInicial = $modalidadElegida === 'DELIVERY' && $productosCentimos < $envioGratisDesdeCentimos ? $envioCentimos : 0;
              $totalInicial = ($productosCentimos + $envioInicial) / 100;
              $igvTotal = round($totalInicial - ($totalInicial / 1.18), 2);
              $baseTotal = round($totalInicial - $igvTotal, 2);
          @endphp

          <form method="POST" action="{{ route('carrito.confirmar') }}" id="form-confirmar"
                data-productos="{{ $productosCentimos }}" data-envio="{{ $envioCentimos }}"
                data-gratis-desde="{{ $envioGratisDesdeCentimos }}">
            @csrf

            {{-- Como recibe el pedido: delivery en Surco o recojo en tienda. --}}
            <fieldset class="entrega">
              <legend class="entrega-titulo">&iquest;C&oacute;mo lo recibes?</legend>
              <div class="entrega-opciones">
                <input type="radio" class="opcion-radio" name="modalidad_entrega" id="entrega-recojo" value="RECOJO"
                       @checked($modalidadElegida === 'RECOJO')>
                <label class="entrega-tarjeta" for="entrega-recojo">
                  <span class="entrega-nombre">Recojo en tienda</span>
                  <span class="entrega-detalle">Gratis &middot; Av. Velasco Astete 1245, Surco</span>
                </label>

                <input type="radio" class="opcion-radio" name="modalidad_entrega" id="entrega-delivery" value="DELIVERY"
                       @checked($modalidadElegida === 'DELIVERY')>
                <label class="entrega-tarjeta" for="entrega-delivery">
                  <span class="entrega-nombre">Delivery en Surco</span>
                  <span class="entrega-detalle">S/ {{ number_format($envioCentimos / 100, 2) }} &middot; gratis desde S/ {{ number_format($envioGratisDesdeCentimos / 100, 2) }}</span>
                </label>
              </div>

              <div class="campo" id="campo-direccion" @if ($modalidadElegida !== 'DELIVERY') hidden @endif>
                <label for="direccion_entrega">Direcci&oacute;n de entrega</label>
                <textarea id="direccion_entrega" name="direccion_entrega" rows="2" maxlength="255"
                          placeholder="Calle, número, dpto. y una referencia">{{ old('direccion_entrega', $direccionPerfil) }}</textarea>
                @if ($direccionPerfil)
                  <p class="campo-ayuda">Es la direcci&oacute;n de tu perfil; c&aacute;mbiala si esta vez va a otro lugar.</p>
                @endif
              </div>
            </fieldset>

            <div class="total-desglose" id="total-carrito" style="margin-top:14px;">
              <p class="total-fila"><span>Productos</span><span>S/ {{ number_format($productosCentimos / 100, 2) }}</span></p>
              <p class="total-fila"><span>Env&iacute;o</span><span id="total-envio">{{ $envioInicial ? 'S/ '.number_format($envioInicial / 100, 2) : 'Gratis' }}</span></p>
              <p class="total-fila"><span>Subtotal (sin IGV)</span><span id="total-base">S/ {{ number_format($baseTotal, 2) }}</span></p>
              <p class="total-fila"><span>IGV 18%</span><span id="total-igv">S/ {{ number_format($igvTotal, 2) }}</span></p>
              <p class="total-fila total-fila--final"><span>Total a pagar</span><span id="total-final">S/ {{ number_format($totalInicial, 2) }}</span></p>
            </div>
            <p class="fiscal-nota">Precios con IGV incluido (D.S. 055-99-EF). Al pagar recibir&aacute;s tu comprobante por correo.</p>

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
            <li>El stock queda reservado {{ \App\Models\Pedido::HORAS_PARA_PAGAR }} horas: si no pagas en ese plazo, el pedido se anula y las unidades vuelven a la tienda.</li>
          </ul>
        </div>
      </div>
    </div>
@endsection

@section('scripts')
  <script>
    // Muestra la direccion solo para delivery y recalcula envio, IGV y total.
    // El servidor vuelve a calcular todo al confirmar; esto es solo la vista previa.
    (function () {
      const form = document.getElementById('form-confirmar');
      if (! form) return;

      const productos = Number(form.dataset.productos);
      const tarifa = Number(form.dataset.envio);
      const gratisDesde = Number(form.dataset.gratisDesde);
      const campoDireccion = document.getElementById('campo-direccion');
      const direccion = document.getElementById('direccion_entrega');
      const soles = function (centimos) { return 'S/ ' + (centimos / 100).toFixed(2); };

      const pintar = function () {
        const elegida = form.querySelector('input[name="modalidad_entrega"]:checked');
        const esDelivery = elegida && elegida.value === 'DELIVERY';

        campoDireccion.hidden = ! esDelivery;
        direccion.required = esDelivery;

        const envio = esDelivery && productos < gratisDesde ? tarifa : 0;
        const total = productos + envio;
        const igv = Math.round(total - total / 1.18);

        document.getElementById('total-envio').textContent = envio ? soles(envio) : 'Gratis';
        document.getElementById('total-base').textContent = soles(total - igv);
        document.getElementById('total-igv').textContent = soles(igv);
        document.getElementById('total-final').textContent = soles(total);
      };

      form.addEventListener('change', pintar);
      pintar();
    })();
  </script>
@endsection
