@extends('layouts.app')

@section('titulo', 'Dashboard | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: dashboard administrativo.')

@section('contenido')
    <div class="contenedor">
      <div class="panel-titulo">
        <h1>Dashboard administrativo</h1>
        <p>Inventario, pedidos, ingreso recurrente y recordatorios de salud.</p>
      </div>

      @include('components.aviso')

      <div class="indicadores" style="margin-bottom:24px;" id="indicadores">
        @foreach ($indicadores as $indicador)
          <div class="indicador">
            <p class="indicador-valor">{{ $indicador['soles'] ? 'S/ '.number_format((float) $indicador['valor'], 2) : $indicador['valor'] }}</p>
            <p class="indicador-etiqueta">{{ $indicador['etiqueta'] }}</p>
          </div>
        @endforeach
      </div>

      <div class="bloque" id="bloque-inventario" style="margin-bottom:22px;">
        <h2>Inventario</h2>
        <p>El sem&aacute;foro compara el stock con el punto de reorden de cada producto.</p>
        <div style="overflow-x:auto;margin-top:14px;">
          <table class="tabla-app">
            <caption class="oculto-visual">Inventario de productos activos</caption>
            <thead>
              <tr><th scope="col">SKU</th><th scope="col">Producto</th><th scope="col">Precio</th>
                  <th scope="col" data-rn="RN-07" data-rn-nota="Stock nunca negativo">Stock</th>
                  <th scope="col">Reorden</th>
                  <th scope="col" data-rn="RN-08" data-rn-nota="Semáforo de reposición">Sem&aacute;foro</th></tr>
            </thead>
            <tbody id="cuerpo-inventario">
              @forelse ($productos as $producto)
                @php $semaforo = $semaforos[$producto->producto_id] ?? 'VERDE'; @endphp
                <tr>
                  <td><code style="font-family:'Courier New',monospace;">{{ $producto->codigo_sku }}</code></td>
                  <td>{{ $producto->nombre }}</td>
                  <td>S/ {{ number_format((float) $producto->precio, 2) }}</td>
                  <td>{{ $producto->stock_actual }}</td>
                  <td>{{ $producto->punto_reorden }}</td>
                  <td><span class="semaforo semaforo-{{ $semaforo }}">{{ $semaforo }}</span></td>
                </tr>
              @empty
                <tr><td colspan="6">No hay productos activos en el cat&aacute;logo.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="rejilla rejilla-2" style="margin-bottom:22px;">
        <div class="bloque" id="bloque-alta">
          <h2>Alta de producto</h2>
          <p>Prueba a repetir un SKU existente o a poner precio cero.</p>
          <form method="POST" action="{{ route('admin.productos.crear') }}">
            @csrf
            <div class="campo" data-rn="RN-05" data-rn-nota="SKU irrepetible">
              <label for="nuevo-sku">C&oacute;digo SKU</label>
              <input type="text" id="nuevo-sku" name="codigo_sku" value="{{ old('codigo_sku') }}" placeholder="ALI-PER-15K">
            </div>
            <div class="campo">
              <label for="nuevo-nombre">Nombre del producto</label>
              <input type="text" id="nuevo-nombre" name="nombre" value="{{ old('nombre') }}">
            </div>
            <div class="campo">
              <label for="nueva-categoria">Categor&iacute;a</label>
              <select id="nueva-categoria" name="categoria">
                <option value="ALIMENTO" @selected(old('categoria') === 'ALIMENTO')>Alimento</option>
                <option value="ACCESORIO" @selected(old('categoria', 'ACCESORIO') === 'ACCESORIO')>Accesorios</option>
                <option value="MEDICAMENTO" @selected(old('categoria') === 'MEDICAMENTO')>Medicamentos</option>
                <option value="ARENA" @selected(old('categoria') === 'ARENA')>Arena</option>
              </select>
            </div>
            <div class="campo" data-rn="RN-06" data-rn-nota="Precio mayor que cero">
              <label for="nuevo-precio">Precio</label>
              <input type="number" id="nuevo-precio" name="precio" step="0.10" value="{{ old('precio', '0') }}">
            </div>
            <div class="campo">
              <label for="nuevo-stock">Stock inicial</label>
              <input type="number" id="nuevo-stock" name="stock_actual" min="0" value="{{ old('stock_actual', '0') }}">
            </div>
            <button type="submit" class="boton" id="btn-alta">Registrar producto</button>
          </form>
        </div>

        <div class="bloque">
          <h2 data-rn="RN-20" data-rn-nota="Aviso 15 días antes">Recordatorios de salud</h2>
          <p>Controles programados dentro de los pr&oacute;ximos 15 d&iacute;as.</p>
          <div style="overflow-x:auto;margin-top:12px;">
            <table class="tabla-app">
              <caption class="oculto-visual">Controles programados en los pr&oacute;ximos 15 d&iacute;as</caption>
              <thead>
                <tr><th scope="col">Mascota</th><th scope="col">Cliente</th>
                    <th scope="col">Fecha</th><th scope="col">Faltan</th></tr>
              </thead>
              <tbody id="cuerpo-recordatorios">
                @forelse ($recordatorios as $recordatorio)
                  <tr>
                    <td>{{ data_get($recordatorio, 'mascota') }}</td>
                    <td>{{ data_get($recordatorio, 'cliente') }}</td>
                    <td>{{ data_get($recordatorio, 'proxima_fecha') }}</td>
                    <td>{{ data_get($recordatorio, 'dias_restantes', data_get($recordatorio, 'dias')) }} d&iacute;as</td>
                  </tr>
                @empty
                  <tr><td colspan="4">Sin controles en los pr&oacute;ximos 15 d&iacute;as.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="bloque" id="bloque-pedidos">
        <h2>Pedidos</h2>
        <p>Cada pedido avanza de PENDIENTE a ENTREGADO, sin saltarse pasos.</p>
        <div style="overflow-x:auto;margin-top:14px;">
          <table class="tabla-app">
            <caption class="oculto-visual">Pedidos registrados y su estado</caption>
            <thead>
              <tr><th scope="col">N.&deg;</th><th scope="col">Cliente</th><th scope="col">Monto</th>
                  <th scope="col" data-rn="RN-14" data-rn-nota="Compra o suscripción">Origen</th>
                  <th scope="col" data-rn="RN-13" data-rn-nota="Secuencia de estados">Estado</th>
                  <th scope="col"><span class="oculto-visual">Acciones</span></th></tr>
            </thead>
            <tbody id="cuerpo-pedidos">
              @forelse ($pedidos as $pedido)
                @php
                    $estadoActual = $pedido->estado instanceof \App\Enums\EstadoPedido
                        ? $pedido->estado : \App\Enums\EstadoPedido::from((string) $pedido->estado);
                    $origen = $pedido->tipo_origen instanceof \App\Enums\TipoOrigen
                        ? $pedido->tipo_origen->value : (string) $pedido->tipo_origen;
                    $siguiente = $estadoActual->siguiente();
                @endphp
                <tr>
                  <td>{{ $pedido->pedido_id }}</td>
                  <td>{{ $clientes[$pedido->cliente_id]->nombre ?? '?' }}</td>
                  <td>S/ {{ number_format((float) $pedido->monto_total, 2) }}</td>
                  <td class="origen-{{ $origen }}">{{ $origen === 'SUSCRIPCION' ? 'Suscripción' : 'Compra directa' }}</td>
                  <td><span class="estado estado-{{ $estadoActual->value }}">{{ $estadoActual->value }}</span></td>
                  <td>
                    @if ($siguiente)
                      <form method="POST" action="{{ route('admin.pedidos.avanzar', $pedido->pedido_id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="boton-mini">Pasar a {{ $siguiente->value }}</button>
                      </form>
                    @else
                      &mdash;
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="6">Todav&iacute;a no hay pedidos registrados.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
@endsection
