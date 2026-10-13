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

      {{-- ALTA DE MASCOTA --}}
      <div class="bloque" style="margin-top:24px;" id="bloque-alta-mascota">
        <h2>Registrar una mascota</h2>
        <p>As&iacute; puedes reservarle citas y contratar su plan de alimento.</p>
        <form method="POST" action="{{ route('mascotas.guardar') }}" style="margin-top:14px;">
          @csrf
          <div class="rejilla rejilla-3">
            <div class="campo">
              <label for="mascota-nombre">Nombre</label>
              <input type="text" id="mascota-nombre" name="nombre" value="{{ old('nombre') }}" maxlength="60" required>
            </div>
            <div class="campo">
              <label for="mascota-especie">Especie</label>
              <select id="mascota-especie" name="especie" required>
                <option value="PERRO" @selected(old('especie') === 'PERRO')>Perro</option>
                <option value="GATO" @selected(old('especie') === 'GATO')>Gato</option>
                <option value="OTRO" @selected(old('especie') === 'OTRO')>Otro</option>
              </select>
            </div>
            <div class="campo">
              <label for="mascota-raza">Raza</label>
              <input type="text" id="mascota-raza" name="raza" value="{{ old('raza') }}" maxlength="60" placeholder="Mestizo">
            </div>
            <div class="campo">
              <label for="mascota-fecha">Fecha de nacimiento</label>
              <input type="date" id="mascota-fecha" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" max="{{ now()->toDateString() }}">
            </div>
            <div class="campo">
              <label for="mascota-peso">Peso (kg)</label>
              <input type="number" id="mascota-peso" name="peso_kg" step="0.1" min="0.1" max="200" value="{{ old('peso_kg') }}">
            </div>
            <div class="campo">
              <label for="mascota-alergias">Alergias</label>
              <input type="text" id="mascota-alergias" name="alergias" value="{{ old('alergias') }}" maxlength="200" placeholder="Ninguna registrada">
            </div>
          </div>
          <p style="margin-top:6px;" data-rn="RN-04" data-rn-nota="La mascota es del cliente">
            <button type="submit" class="boton">Registrar mascota</button>
          </p>
        </form>
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
        <p data-rn="RN-22" data-rn-nota="Un plan por mascota">
          Recibes el producto en casa cada 30 d&iacute;as, sin volver a pedirlo.
          Cada mascota tiene un solo plan; puedes pausarlo o cancelarlo cuando quieras.
        </p>

        @php
            // RN-22: solo pueden contratar las mascotas que no tienen plan vigente.
            $libres = $mascotas->reject(fn ($m) => isset($planVigentePorMascota[$m->mascota_id]));
        @endphp

        @if ($mascotas->isEmpty())
          <p class="nota-regla" style="margin-top:14px;">
            Necesitas tener una mascota registrada para contratar un plan.
          </p>
        @elseif ($libres->isEmpty())
          <p class="nota-regla" style="margin-top:14px;">
            Todas tus mascotas ya tienen su plan. Para cambiar uno, canc&eacute;lalo en la tabla
            de arriba y vuelve a contratarlo aqu&iacute;.
          </p>
        @else
          <form method="POST" action="{{ route('suscripciones.contratar') }}" id="form-plan"
                data-primer-despacho="{{ $primerDespacho->format('d/m/Y') }}">
            @csrf

            <div class="paso-plan">
              <p class="paso-titulo"><span class="paso-numero">1</span> Para qui&eacute;n es</p>
              <div class="mascotas-opciones">
                @foreach ($mascotas as $mascota)
                  @php $planVigente = $planVigentePorMascota[$mascota->mascota_id] ?? null; @endphp
                  <input type="radio" class="opcion-radio" name="mascota_id" id="mascota-{{ $mascota->mascota_id }}"
                         value="{{ $mascota->mascota_id }}" required
                         {{-- Nada premarcado: el cliente elige a conciencia; solo se
                              recupera lo que ya habia elegido si la validacion fallo. --}}
                         @checked($planVigente === null && (string) old('mascota_id') === (string) $mascota->mascota_id)
                         {{-- RN-22: con plan vigente no se puede elegir. --}}
                         @disabled($planVigente !== null)
                         data-nombre="{{ $mascota->nombre }}">
                  <label class="mascota-chip" for="mascota-{{ $mascota->mascota_id }}"
                         @if ($planVigente) title="{{ $mascota->nombre }} ya tiene su plan: {{ $planVigente }}" @endif>
                    <span class="mascota-avatar" aria-hidden="true">{{ mb_substr($mascota->nombre, 0, 1) }}</span>
                    <span>
                      <span class="mascota-nombre">{{ $mascota->nombre }}</span><br>
                      @if ($planVigente)
                        <span class="mascota-plan">Ya tiene {{ $planVigente }}</span>
                      @else
                        <span class="mascota-especie">{{ $mascota->especie instanceof \App\Enums\Especie ? $mascota->especie->value : $mascota->especie }}</span>
                      @endif
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
                  <option value="" disabled @selected(! old('producto_id'))>Elige el producto</option>
                  @foreach ($catalogo as $producto)
                    @php $etiqueta = $producto->nombre.' — S/ '.number_format((float) $producto->precio, 2); @endphp
                    <option value="{{ $producto->producto_id }}"
                            @selected((string) old('producto_id') === (string) $producto->producto_id)
                            data-nombre="{{ $producto->nombre }}"
                            data-etiqueta="{{ $etiqueta }}">{{ $etiqueta }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="paso-plan" data-rn="RN-16" data-rn-nota="Nace con su proximo despacho">
              <p class="paso-titulo"><span class="paso-numero">3</span> Qu&eacute; plan</p>
              <div class="planes-opciones">
                @foreach ($planes as $nombrePlan => $datos)
                  <input type="radio" class="opcion-radio" name="plan" id="plan-{{ $nombrePlan }}"
                         value="{{ $nombrePlan }}" required @checked(old('plan') === $nombrePlan)
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
              <button type="submit" class="boton" disabled>Contratar plan</button>
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
      const boton = form.querySelector('button[type="submit"]');

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

        // El boton solo se habilita cuando las tres elecciones estan hechas.
        // Las mascotas con plan vigente vienen deshabilitadas (RN-22).
        boton.disabled = true;

        const opcion = producto.value !== '' ? producto.options[producto.selectedIndex] : null;
        const faltan = [];
        if (! mascota) faltan.push('para quien es');
        if (! opcion) faltan.push('que producto recibe');
        if (! plan) faltan.push('que plan');

        if (faltan.length) {
          resumen.textContent = 'Elige ' + faltan.join(', ') + ' para ver el resumen.';
          return;
        }

        boton.disabled = false;

        const unidades = Number(plan.dataset.unidades);

        const nota = document.createElement('small');
        nota.className = 'resumen-nota';
        nota.textContent = 'Primer despacho el ' + form.dataset.primerDespacho + '.';

        resumen.replaceChildren(
          fuerte(mascota.dataset.nombre),
          document.createTextNode(' recibe '),
          fuerte(unidades + (unidades === 1 ? ' unidad' : ' unidades')),
          document.createTextNode(' de '),
          fuerte(opcion.dataset.nombre),
          document.createTextNode(' cada 30 dias, por '),
          fuerte('S/ ' + plan.dataset.monto),
          document.createTextNode(' al mes.'),
          nota,
        );
      };

      form.addEventListener('change', pintar);

      // Un doble clic mandaria dos contrataciones a la vez; se envia una sola.
      form.addEventListener('submit', function () {
        boton.disabled = true;
        boton.textContent = 'Contratando...';
      });

      pintar();
    })();
  </script>
@endsection
