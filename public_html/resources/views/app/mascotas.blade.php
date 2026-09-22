@extends('layouts.app')

@section('titulo', 'Mis mascotas | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: mis mascotas.')

@section('contenido')
    @php
        $porMascota = $mascotas->keyBy('mascota_id');
    @endphp
    <div class="contenedor">
      <div class="panel-titulo">
        <h1>Mis mascotas y suscripciones</h1>
        <p>Cada mascota pertenece a una sola cuenta: aqu&iacute; ves &uacute;nicamente las tuyas.</p>
      </div>

      @include('components.aviso')

      <div class="rejilla rejilla-3" id="lista-mascotas" data-rn="RN-04" data-rn-nota="Mascotas del cliente">
        @forelse ($mascotas as $mascota)
          <article class="tarjeta">
            <h2 class="producto-nombre">{{ $mascota->nombre }}</h2>
            <p class="producto-sku">{{ $mascota->especie instanceof \App\Enums\Especie ? $mascota->especie->value : $mascota->especie }} &middot; {{ $mascota->raza }}</p>
            <ul class="lista-simple" style="margin-top:10px;">
              <li>Peso: {{ $mascota->peso_kg }} kg</li>
              <li>Alergias: {{ $mascota->alergias ?: 'ninguna registrada' }}</li>
            </ul>
          </article>
        @empty
          <p class="nota-regla">No tienes mascotas registradas.</p>
        @endforelse
      </div>

      <div class="bloque" style="margin-top:24px;">
        <h2>Suscripci&oacute;n mensual</h2>
        <p>Puedes pausarla o cancelarla en cualquier momento.</p>
        <div class="tabla-scroll" style="margin-top:14px;">
          <table class="tabla-app">
            <caption class="oculto-visual">Suscripciones mensuales de la cuenta</caption>
            <thead>
              <tr><th scope="col">Plan</th><th scope="col">Mascota</th><th scope="col">Producto</th>
                  <th scope="col">Monto</th>
                  <th scope="col" data-rn="RN-16" data-rn-nota="Frecuencia y próximo despacho">Pr&oacute;ximo despacho</th>
                  <th scope="col">Estado</th>
                  <th scope="col" data-rn="RN-15" data-rn-nota="Pausar o cancelar">Acciones</th></tr>
            </thead>
            <tbody id="cuerpo-suscripciones">
              @forelse ($suscripciones as $suscripcion)
                @php
                    $estado = $suscripcion->estado instanceof \App\Enums\EstadoSuscripcion
                        ? $suscripcion->estado->value : (string) $suscripcion->estado;
                    $activa = $estado === 'ACTIVA';
                    $despacho = \Illuminate\Support\Carbon::parse($suscripcion->proximo_despacho);
                    $dias = (int) \Illuminate\Support\Carbon::today()->diffInDays($despacho, false);
                @endphp
                <tr>
                  <td data-label="Plan">{{ $suscripcion->plan }}</td>
                  <td data-label="Mascota">{{ $porMascota[$suscripcion->mascota_id]->nombre ?? '?' }}</td>
                  <td data-label="Producto">{{ $productos[$suscripcion->producto_id]->nombre ?? '?' }}</td>
                  <td data-label="Monto">S/ {{ number_format((float) $suscripcion->monto_mensual, 2) }}</td>
                  <td data-label="Pr&oacute;ximo despacho">{{ $despacho->format('Y-m-d') }} <small>(en {{ $dias }} d&iacute;as, cada {{ $suscripcion->frecuencia_dias }})</small></td>
                  <td data-label="Estado"><span class="estado estado-{{ $estado }}">{{ $estado }}</span></td>
                  <td>
                    @if ($estado === 'CANCELADA')
                      &mdash;
                    @else
                      <form method="POST" action="{{ route('suscripciones.estado', $suscripcion->suscripcion_id) }}" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="{{ $activa ? 'PAUSADA' : 'ACTIVA' }}">
                        <button type="submit" class="boton-mini">{{ $activa ? 'Pausar' : 'Reanudar' }}</button>
                      </form>
                      <form method="POST" action="{{ route('suscripciones.estado', $suscripcion->suscripcion_id) }}" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="CANCELADA">
                        <button type="submit" class="boton-mini">Cancelar</button>
                      </form>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="7">Todav&iacute;a no tienes ninguna suscripci&oacute;n. Contrata una aqu&iacute; abajo.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="bloque" style="margin-top:24px;">
        <h2>Contratar un plan mensual</h2>
        <p data-rn="RN-22" data-rn-nota="Sin planes repetidos">
          Recibes el producto en casa cada 30 d&iacute;as, sin volver a pedirlo.
          Puedes pausarlo o cancelarlo cuando quieras.
        </p>

        @if ($mascotas->isEmpty())
          <p class="nota-regla" style="margin-top:14px;">
            Necesitas tener una mascota registrada para contratar un plan.
          </p>
        @else
          <form method="POST" action="{{ route('suscripciones.contratar') }}" id="form-plan">
            @csrf

            <div class="paso-plan">
              <p class="paso-titulo"><span class="paso-numero">1</span> Para qui&eacute;n es</p>
              <div class="mascotas-opciones">
                @foreach ($mascotas as $indice => $mascota)
                  <input type="radio" class="opcion-radio" name="mascota_id" id="mascota-{{ $mascota->mascota_id }}"
                         value="{{ $mascota->mascota_id }}" @checked($indice === 0)
                         data-nombre="{{ $mascota->nombre }}"
                         {{-- RN-22: lo que esta mascota ya recibe no se puede volver a contratar. --}}
                         data-vigentes="{{ json_encode($vigentesPorMascota[$mascota->mascota_id] ?? []) }}">
                  <label class="mascota-chip" for="mascota-{{ $mascota->mascota_id }}">
                    <span class="mascota-avatar" aria-hidden="true">{{ mb_substr($mascota->nombre, 0, 1) }}</span>
                    <span>
                      <span class="mascota-nombre">{{ $mascota->nombre }}</span><br>
                      <span class="mascota-especie">{{ $mascota->especie instanceof \App\Enums\Especie ? $mascota->especie->value : $mascota->especie }}</span>
                    </span>
                  </label>
                @endforeach
              </div>
            </div>

            <div class="paso-plan">
              <p class="paso-titulo"><span class="paso-numero">2</span> Qu&eacute; recibe cada mes</p>
              <div class="campo" style="max-width:420px;">
                <label class="oculto-visual" for="producto_id">Producto del despacho</label>
                <select id="producto_id" name="producto_id" required>
                  @foreach ($catalogo as $producto)
                    @php $etiqueta = $producto->nombre.' — S/ '.number_format((float) $producto->precio, 2); @endphp
                    <option value="{{ $producto->producto_id }}"
                            data-nombre="{{ $producto->nombre }}"
                            data-etiqueta="{{ $etiqueta }}">{{ $etiqueta }}</option>
                  @endforeach
                </select>
                <p class="error-campo" id="aviso-productos" role="status" hidden></p>
              </div>
            </div>

            <div class="paso-plan" data-rn="RN-16" data-rn-nota="Nace con su proximo despacho">
              <p class="paso-titulo"><span class="paso-numero">3</span> Qu&eacute; plan</p>
              <div class="planes-opciones">
                @foreach ($planes as $nombrePlan => $datos)
                  <input type="radio" class="opcion-radio" name="plan" id="plan-{{ $nombrePlan }}"
                         value="{{ $nombrePlan }}" @checked($loop->first)
                         data-monto="{{ number_format((float) $datos['monto'], 2) }}"
                         data-unidades="{{ $datos['unidades'] }}">
                  <label class="plan-tarjeta" for="plan-{{ $nombrePlan }}">
                    <span class="plan-nombre">{{ $nombrePlan }}</span>
                    <p class="plan-precio">S/ {{ number_format((float) $datos['monto'], 2) }}</p>
                    <span class="plan-periodo">al mes</span>
                    <p class="plan-detalle">
                      <strong>{{ $datos['unidades'] }}</strong>
                      {{ $datos['unidades'] === 1 ? 'unidad' : 'unidades' }} en cada despacho,
                      a domicilio cada 30 d&iacute;as.
                    </p>
                  </label>
                @endforeach
              </div>
            </div>

            <div class="resumen-plan">
              <p id="resumen-plan">Elige las opciones de arriba para ver el resumen.</p>
              <button type="submit" class="boton">Contratar plan</button>
            </div>
          </form>
        @endif
      </div>
    </div>
@endsection

@section('scripts')
  <script>
    // Resume en una frase lo que se va a contratar, antes de confirmar.
    (function () {
      const form = document.getElementById('form-plan');
      if (! form) return;

      const resumen = document.getElementById('resumen-plan');
      const producto = document.getElementById('producto_id');
      const aviso = document.getElementById('aviso-productos');
      const boton = form.querySelector('button[type="submit"]');

      /*
       * RN-22 en la propia pantalla: lo que la mascota elegida ya recibe se
       * deshabilita en la lista. El servidor lo sigue validando igual; esto
       * solo evita que la persona elija algo que iba a ser rechazado.
       */
      const ajustarProductos = function (mascota) {
        const vigentes = JSON.parse(mascota.dataset.vigentes || '[]');
        let disponibles = 0;

        Array.from(producto.options).forEach(function (opcion) {
          const yaLoRecibe = vigentes.indexOf(Number(opcion.value)) !== -1;

          opcion.disabled = yaLoRecibe;
          opcion.textContent = yaLoRecibe
            ? opcion.dataset.nombre + ' — ya lo recibe'
            : opcion.dataset.etiqueta;

          if (! yaLoRecibe) disponibles++;
        });

        // Si lo que estaba elegido quedo deshabilitado, saltamos al primero libre.
        const elegido = producto.options[producto.selectedIndex];

        if (! elegido || elegido.disabled) {
          const libre = Array.from(producto.options).find(function (o) { return ! o.disabled; });
          producto.value = libre ? libre.value : '';
        }

        const sinOpciones = disponibles === 0;
        aviso.textContent = sinOpciones
          ? mascota.dataset.nombre + ' ya recibe todos los productos del catalogo.'
          : '';
        aviso.hidden = ! sinOpciones;
        boton.disabled = sinOpciones;

        return ! sinOpciones;
      };

      // Se arma con nodos de texto en vez de innerHTML: los nombres vienen de
      // la base de datos y no deben poder inyectar marcado.
      const fuerte = function (texto) {
        const el = document.createElement('strong');
        el.textContent = texto;
        return el;
      };

      const pintar = function () {
        const mascota = form.querySelector('input[name="mascota_id"]:checked');
        const plan = form.querySelector('input[name="plan"]:checked');

        if (! mascota || ! plan) return;

        if (! ajustarProductos(mascota)) {
          resumen.textContent = 'No queda ningun producto por contratar para ' + mascota.dataset.nombre + '.';
          return;
        }

        const opcion = producto.options[producto.selectedIndex];
        if (! opcion) return;

        const unidades = Number(plan.dataset.unidades);

        resumen.replaceChildren(
          fuerte(mascota.dataset.nombre),
          document.createTextNode(' recibe '),
          fuerte(unidades + (unidades === 1 ? ' unidad' : ' unidades')),
          document.createTextNode(' de '),
          fuerte(opcion.dataset.nombre),
          document.createTextNode(' cada 30 dias, por '),
          fuerte('S/ ' + plan.dataset.monto),
          document.createTextNode(' al mes.'),
        );
      };

      form.addEventListener('change', pintar);
      pintar();
    })();
  </script>
@endsection
