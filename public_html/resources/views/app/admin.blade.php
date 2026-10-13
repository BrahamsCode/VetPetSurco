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
        <div class="tabla-scroll" style="margin-top:14px;">
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
                  <td data-label="SKU"><code style="font-family:'Courier New',monospace;">{{ $producto->codigo_sku }}</code></td>
                  <td data-label="Producto">{{ $producto->nombre }}</td>
                  <td data-label="Precio">S/ {{ number_format((float) $producto->precio, 2) }}</td>
                  <td data-label="Stock">{{ $producto->stock_actual }}</td>
                  <td data-label="Reorden">{{ $producto->punto_reorden }}</td>
                  <td data-label="Sem&aacute;foro"><span class="semaforo semaforo-{{ $semaforo }}">{{ $semaforo }}</span></td>
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
          <div class="tabla-scroll" style="margin-top:12px;">
            <table class="tabla-app">
              <caption class="oculto-visual">Controles programados en los pr&oacute;ximos 15 d&iacute;as</caption>
              <thead>
                <tr><th scope="col">Mascota</th><th scope="col">Cliente</th>
                    <th scope="col">Fecha</th><th scope="col">Faltan</th></tr>
              </thead>
              <tbody id="cuerpo-recordatorios">
                @forelse ($recordatorios as $recordatorio)
                  <tr>
                    <td data-label="Mascota">{{ data_get($recordatorio, 'mascota') }}</td>
                    <td data-label="Cliente">{{ data_get($recordatorio, 'cliente') }}</td>
                    <td data-label="Fecha">{{ data_get($recordatorio, 'proxima_fecha') }}</td>
                    <td data-label="Faltan">{{ data_get($recordatorio, 'dias_restantes', data_get($recordatorio, 'dias')) }} d&iacute;as</td>
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
        <p data-rn="RN-13" data-rn-nota="Secuencia de estados">
          Un pedido entra aqu&iacute; cuando la pasarela aprueba el pago. De ah&iacute; avanza un paso a la vez:
          preparar, enviar (o dejar listo para recoger) y entregar.
        </p>

        {{-- Tablero del dia: cada columna es una etapa con su accion. --}}
        <div class="tablero">
          @foreach ([
              ['porPreparar', 'Por preparar', 'Pagados: arma el pedido y envíalo o déjalo listo.'],
              ['enCurso', 'En camino / listo para recoger', 'Márcalos al entregarlos.'],
              ['entregadosHoy', 'Entregados hoy', 'Lo que ya llegó a su dueño.'],
          ] as [$clave, $tituloEtapa, $ayudaEtapa])
            <section class="tablero-columna" aria-labelledby="etapa-{{ $clave }}">
              <h3 id="etapa-{{ $clave }}" class="agenda-grupo">{{ $tituloEtapa }} <span class="agenda-cuenta">{{ $etapas[$clave]->count() }}</span></h3>
              <p class="agenda-ayuda">{{ $ayudaEtapa }}</p>
              @if ($etapas[$clave]->isEmpty())
                <p class="agenda-vacia">Nada por aqu&iacute;.</p>
              @else
                <ul class="tablero-lista">
                  @foreach ($etapas[$clave] as $pedido)
                    @include('app._pedido-admin')
                  @endforeach
                </ul>
              @endif
            </section>
          @endforeach
        </div>

        {{-- Pedidos sin pagar: no se preparan; se anulan solos a las 48 h. --}}
        <h3 class="agenda-grupo agenda-grupo-alerta" style="margin-top:26px;">
          Esperando pago <span class="agenda-cuenta">{{ $etapas['porCobrar']->count() }}</span>
        </h3>
        <p class="agenda-ayuda">
          No se preparan hasta que la pasarela apruebe el cobro. Si no se pagan en
          {{ \App\Models\Pedido::HORAS_PARA_PAGAR }} horas se anulan solos y el stock vuelve.
        </p>
        @if ($etapas['porCobrar']->isEmpty())
          <p class="agenda-vacia">No hay pedidos esperando pago.</p>
        @else
          <ul class="tablero-lista tablero-lista-fila">
            @foreach ($etapas['porCobrar'] as $pedido)
              @include('app._pedido-admin')
            @endforeach
          </ul>
        @endif

        <details class="ficha-antecedentes" style="margin-top:22px;">
          <summary>Historial de todos los pedidos ({{ $pedidos->count() }})</summary>
        <div class="tabla-scroll" style="margin-top:14px;">
          <table class="tabla-app">
            <caption class="oculto-visual">Pedidos registrados y su estado</caption>
            <thead>
              <tr><th scope="col">N.&deg;</th><th scope="col">Fecha</th><th scope="col">Cliente</th><th scope="col">Total</th>
                  <th scope="col" data-rn="RN-14" data-rn-nota="Compra o suscripción">Origen</th>
                  <th scope="col">Entrega</th>
                  <th scope="col">Estado</th></tr>
            </thead>
            <tbody id="cuerpo-pedidos">
              @forelse ($pedidos as $pedido)
                @php
                    $estadoActual = $pedido->estadoActual();
                    $origen = $pedido->tipo_origen instanceof \App\Enums\TipoOrigen
                        ? $pedido->tipo_origen->value : (string) $pedido->tipo_origen;
                @endphp
                <tr>
                  <td data-label="N.&deg;">{{ $pedido->pedido_id }}</td>
                  <td data-label="Fecha">{{ $pedido->fecha_pedido->format('d/m/Y') }}</td>
                  <td data-label="Cliente">{{ $pedido->cliente->nombre ?? '?' }}</td>
                  <td data-label="Total">S/ {{ number_format((float) $pedido->totalACobrar(), 2) }}</td>
                  <td data-label="Origen" class="origen-{{ $origen }}">{{ $origen === 'SUSCRIPCION' ? 'Suscripción' : 'Compra directa' }}</td>
                  <td data-label="Entrega">{{ $pedido->modalidad()->etiqueta() }}</td>
                  <td data-label="Estado"><span class="estado estado-{{ $estadoActual->value }}">{{ $pedido->etiquetaEstado() }}</span></td>
                </tr>
              @empty
                <tr><td colspan="7">Todav&iacute;a no hay pedidos registrados.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        </details>
      </div>
    </div>
@endsection
