@extends('layouts.app')

@section('titulo', 'Ficha clínica | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: ficha clínica.')

@section('contenido')
    <div class="contenedor">
      <div class="panel-titulo">
        <h1>Ficha cl&iacute;nica</h1>
        <p>{{ ucfirst(today()->translatedFormat('l j \d\e F')) }} &middot; Registra la atenci&oacute;n de cada cita y programa el pr&oacute;ximo control.</p>
      </div>

      @include('components.aviso')

      <div class="indicadores clinica-indicadores">
        <div class="indicador">
          <p class="indicador-valor">{{ $deHoy->count() }}</p>
          <p class="indicador-etiqueta">Citas de hoy</p>
        </div>
        <div class="indicador{{ $pendientes->isNotEmpty() ? ' indicador-alerta' : '' }}">
          <p class="indicador-valor">{{ $pendientes->count() }}</p>
          <p class="indicador-etiqueta">Pendientes de cerrar</p>
        </div>
        <div class="indicador">
          <p class="indicador-valor">{{ $proximas->count() }}</p>
          <p class="indicador-etiqueta">Pr&oacute;ximas</p>
        </div>
      </div>

      <div class="clinica-rejilla">
        {{-- Agenda: primero lo que exige accion. --}}
        <section class="bloque" aria-labelledby="titulo-agenda">
          <h2 id="titulo-agenda">Agenda</h2>
          <p>Pulsa <strong>Atender</strong> para abrir la ficha de la mascota.</p>

          @if ($pendientes->isNotEmpty())
            <h3 class="agenda-grupo agenda-grupo-alerta" data-rn="RN-18" data-rn-nota="Toda cita registra su desenlace">
              Pendientes de cerrar <span class="agenda-cuenta">{{ $pendientes->count() }}</span>
            </h3>
            <p class="agenda-ayuda">Citas de d&iacute;as anteriores sin desenlace: reg&iacute;stralas o marca la inasistencia.</p>
            <ul class="agenda">
              @foreach ($pendientes as $cita)
                @include('app._cita-agenda')
              @endforeach
            </ul>
          @endif

          <h3 class="agenda-grupo">Hoy <span class="agenda-cuenta">{{ $deHoy->count() }}</span></h3>
          @if ($deHoy->isEmpty())
            <p class="agenda-vacia">No tienes citas para hoy.</p>
          @else
            <ul class="agenda">
              @foreach ($deHoy as $cita)
                @include('app._cita-agenda')
              @endforeach
            </ul>
          @endif

          <h3 class="agenda-grupo">Pr&oacute;ximas <span class="agenda-cuenta">{{ $proximas->count() }}</span></h3>
          @if ($proximas->isEmpty())
            <p class="agenda-vacia">No hay citas programadas para los pr&oacute;ximos d&iacute;as.</p>
          @else
            <ul class="agenda">
              @foreach ($proximas as $cita)
                @include('app._cita-agenda')
              @endforeach
            </ul>
          @endif
        </section>

        {{-- Registro de la atencion de la cita elegida. --}}
        <section class="bloque clinica-registro" aria-labelledby="titulo-registro" data-rn="RN-19" data-rn-nota="Una atención, un registro">
          <h2 id="titulo-registro">Registrar atenci&oacute;n</h2>

          @if (! $citaElegida)
            <div class="clinica-vacio">
              @if ($avisoEleccion)
                <p class="clinica-vacio-titulo">No se puede atender esa cita</p>
                <p>{{ $avisoEleccion }}</p>
              @else
                <p class="clinica-vacio-titulo">Ninguna cita seleccionada</p>
                <p>Elige una cita de hoy o una pendiente con el bot&oacute;n <strong>Atender</strong>.
                   Aqu&iacute; ver&aacute;s la ficha de la mascota y sus atenciones anteriores.</p>
              @endif
            </div>
          @else
            @php
                $mascota = $citaElegida->mascota;
                $especie = $mascota?->especie instanceof \App\Enums\Especie ? $mascota->especie->etiqueta() : (string) ($mascota?->especie ?? '');
                $servicioElegido = $citaElegida->servicio instanceof \App\Enums\Servicio ? $citaElegida->servicio->etiqueta() : (string) $citaElegida->servicio;
                $edad = null;
                if ($mascota?->fecha_nacimiento) {
                    $anios = (int) $mascota->fecha_nacimiento->diffInYears(today());
                    $meses = (int) $mascota->fecha_nacimiento->diffInMonths(today());
                    $edad = $anios >= 1 ? $anios.' '.($anios === 1 ? 'año' : 'años') : $meses.' '.($meses === 1 ? 'mes' : 'meses');
                }
                $datosMascota = array_filter([
                    $especie,
                    $mascota?->raza,
                    $edad,
                    $mascota?->peso_kg !== null ? number_format((float) $mascota->peso_kg, 1).' kg' : null,
                ]);
            @endphp

            <div class="ficha">
              <div class="ficha-cabecera">
                <span class="mascota-avatar" aria-hidden="true">{{ mb_substr($mascota->nombre ?? '?', 0, 1) }}</span>
                <div>
                  <p class="ficha-nombre">{{ $mascota->nombre ?? '?' }}</p>
                  <p class="ficha-sub">{{ implode(' · ', $datosMascota) }}</p>
                </div>
              </div>

              <dl class="ficha-datos">
                <div><dt>Cita</dt><dd>{{ $servicioElegido }} &middot; {{ $citaElegida->fecha_hora->isToday() ? 'hoy' : $citaElegida->fecha_hora->format('d/m/Y') }} {{ $citaElegida->fecha_hora->format('H:i') }}</dd></div>
                <div><dt>Due&ntilde;o</dt><dd>{{ $mascota?->cliente?->nombre ?? '—' }}@if ($mascota?->cliente?->telefono) &middot; <a href="tel:{{ $mascota->cliente->telefono }}">{{ $mascota->cliente->telefono }}</a>@endif</dd></div>
              </dl>

              @if (filled($mascota?->alergias))
                <p class="ficha-alergias" role="note"><strong>Alergias:</strong> {{ $mascota->alergias }}</p>
              @else
                <p class="ficha-sin-alergias">Sin alergias registradas.</p>
              @endif

              <details class="ficha-antecedentes" @if ($antecedentes->isNotEmpty()) open @endif>
                <summary>Atenciones anteriores ({{ $antecedentes->count() }})</summary>
                @if ($antecedentes->isEmpty())
                  <p class="agenda-vacia">Es su primera atenci&oacute;n registrada.</p>
                @else
                  <ul>
                    @foreach ($antecedentes as $previa)
                      <li>
                        <span class="antecedente-fecha">{{ $previa->fecha_atencion->format('d/m/Y') }}</span>
                        {{ $previa->diagnostico }}
                        @if ($previa->vacuna_aplicada) <span class="antecedente-vacuna">Vacuna: {{ $previa->vacuna_aplicada }}</span> @endif
                      </li>
                    @endforeach
                  </ul>
                @endif
              </details>
            </div>

            <form method="POST" action="{{ route('clinica.atender', $citaElegida->cita_id) }}" class="clinica-form">
              @csrf
              <div class="campo">
                <label for="diagnostico">Diagn&oacute;stico</label>
                <textarea id="diagnostico" name="diagnostico" rows="3" maxlength="2000" required>{{ old('diagnostico') }}</textarea>
              </div>
              <div class="campo">
                <label for="tratamiento">Tratamiento</label>
                <textarea id="tratamiento" name="tratamiento" rows="3" maxlength="2000" required>{{ old('tratamiento') }}</textarea>
              </div>
              <div class="campo">
                <label for="vacuna">Vacuna aplicada <span class="campo-opcional">(opcional)</span></label>
                <input type="text" id="vacuna" name="vacuna_aplicada" maxlength="100" value="{{ old('vacuna_aplicada') }}"
                       placeholder="{{ $citaElegida->servicio === \App\Enums\Servicio::VACUNACION ? 'Ej.: Antirrábica' : '' }}">
              </div>
              <div class="campo" data-rn="RN-20" data-rn-nota="Próximo control">
                <label for="proxima">Pr&oacute;ximo control <span class="campo-opcional">(opcional)</span></label>
                <input type="date" id="proxima" name="proxima_fecha" min="{{ today()->addDay()->toDateString() }}"
                       value="{{ old('proxima_fecha', $proximaSugerida) }}">
                <p class="campo-ayuda">El due&ntilde;o recibe un recordatorio 15 d&iacute;as antes. D&eacute;jalo vac&iacute;o si no necesita control.</p>
              </div>
              <div class="clinica-form-acciones">
                <button type="submit" class="boton">Guardar en la historia cl&iacute;nica</button>
                <a class="boton-mini" href="{{ route('clinica') }}">Cancelar</a>
              </div>
            </form>
          @endif

          <p class="nota-regla">Una cita ya atendida no admite un segundo registro cl&iacute;nico:
             la relaci&oacute;n con <code>historias_clinicas</code> es de uno a uno.</p>
        </section>
      </div>

      <div class="bloque" style="margin-top:24px;">
        <div class="bloque-cabecera">
          <div>
            <h2>&Uacute;ltimas atenciones</h2>
            <p>Las {{ $historias->count() }} m&aacute;s recientes que registraste. Toca una para ver el detalle.</p>
          </div>
          @if ($totalHistorias > 0)
            <a class="boton-mini" href="{{ route('clinica.historia') }}">Ver toda la historia ({{ $totalHistorias }})</a>
          @endif
        </div>

        {{-- Lista desplegable y no tabla: diagnostico y tratamiento son texto
             libre y en una tabla cada fila crecia al alto del texto mas largo. --}}
        @if ($historias->isEmpty())
          <p class="agenda-vacia" style="margin-top:12px;">Sin atenciones registradas.</p>
        @else
          <ul class="historial" id="cuerpo-historias">
            @foreach ($historias as $historia)
              @include('app._historial-item')
            @endforeach
          </ul>
        @endif
      </div>
    </div>
@endsection

@section('scripts')
  <script>
    // Marcar una inasistencia no se puede deshacer (RN-18): se pide confirmacion.
    document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
      form.addEventListener('submit', function (evento) {
        if (! window.confirm(form.dataset.confirmar)) evento.preventDefault();
      });
    });
  </script>
@endsection
