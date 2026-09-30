@extends('layouts.app')

@section('titulo', 'Ficha clínica | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: ficha clínica.')

@section('contenido')
    <div class="contenedor">
      <div class="panel-titulo">
        <h1>Ficha cl&iacute;nica</h1>
        <p>Registra la atenci&oacute;n de cada cita y programa el pr&oacute;ximo control.</p>
      </div>

      @include('components.aviso')

      <div class="rejilla rejilla-2">
        <div class="bloque">
          <h2>Citas de hoy y pr&oacute;ximas</h2>
          <p>Selecciona una cita para registrar su atenci&oacute;n.</p>
          <div class="tabla-scroll" style="margin-top:12px;">
            <table class="tabla-app">
              <caption class="oculto-visual">Agenda de citas asignadas al veterinario</caption>
              <thead>
                <tr><th scope="col">Mascota</th><th scope="col">Servicio</th><th scope="col">Fecha</th>
                    <th scope="col" data-rn="RN-18" data-rn-nota="Desenlace de la cita">Estado</th>
                    <th scope="col"><span class="oculto-visual">Acciones</span></th></tr>
              </thead>
              <tbody id="cuerpo-agenda">
                @forelse ($citas as $cita)
                  @php
                      $momento = \Illuminate\Support\Carbon::parse($cita->fecha_hora);
                      $estado = $cita->estado instanceof \App\Enums\EstadoCita ? $cita->estado->value : (string) $cita->estado;
                      $servicioCita = $cita->servicio instanceof \App\Enums\Servicio ? $cita->servicio->value : (string) $cita->servicio;
                      $elegida = $citaElegida !== null && (int) $citaElegida->cita_id === (int) $cita->cita_id;
                  @endphp
                  <tr @if ($elegida) style="background-color:var(--menta);" @endif>
                    <td data-label="Mascota">{{ $mascotas[$cita->mascota_id]->nombre ?? '?' }}</td>
                    <td data-label="Servicio">{{ $servicioCita }}</td>
                    <td data-label="Fecha">{{ $momento->format('Y-m-d') }} &middot; {{ $momento->format('H:i') }}</td>
                    <td data-label="Estado"><span class="estado estado-{{ $estado }}">{{ $estado }}</span></td>
                    <td>
                      @if ($estado === 'RESERVADA')
                        <a class="boton-mini" href="{{ route('clinica', ['cita' => $cita->cita_id]) }}">Atender</a>
                        <form method="POST" action="{{ route('clinica.desenlace', $cita->cita_id) }}" style="display:inline;">
                          @csrf
                          @method('PATCH')
                          <input type="hidden" name="desenlace" value="NO_ASISTIO">
                          <button type="submit" class="boton-mini">No asisti&oacute;</button>
                        </form>
                      @else
                        &mdash;
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="5">No tienes citas asignadas.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="bloque" data-rn="RN-19" data-rn-nota="Una atención, un registro">
          <h2>Registrar atenci&oacute;n</h2>
          @if ($citaElegida)
            @php
                $momentoElegido = \Illuminate\Support\Carbon::parse($citaElegida->fecha_hora);
                $servicioElegido = $citaElegida->servicio instanceof \App\Enums\Servicio
                    ? $citaElegida->servicio->value : (string) $citaElegida->servicio;
            @endphp
            <p id="cita-elegida">Atendiendo a <strong>{{ $mascotas[$citaElegida->mascota_id]->nombre ?? '?' }}</strong> &middot; {{ $servicioElegido }} &middot; {{ $momentoElegido->format('Y-m-d H:i') }}</p>
          @else
            <p id="cita-elegida">Ninguna cita seleccionada.</p>
          @endif
          <form method="POST" action="{{ $citaElegida ? route('clinica.atender', $citaElegida->cita_id) : '#' }}">
            @csrf
            <div class="campo">
              <label for="diagnostico">Diagn&oacute;stico</label>
              <textarea id="diagnostico" name="diagnostico" rows="3">{{ old('diagnostico') }}</textarea>
            </div>
            <div class="campo">
              <label for="tratamiento">Tratamiento</label>
              <textarea id="tratamiento" name="tratamiento" rows="2">{{ old('tratamiento') }}</textarea>
            </div>
            <div class="campo">
              <label for="vacuna">Vacuna aplicada (opcional)</label>
              <input type="text" id="vacuna" name="vacuna_aplicada" value="{{ old('vacuna_aplicada') }}">
            </div>
            <div class="campo" data-rn="RN-20" data-rn-nota="Próximo control">
              <label for="proxima">Fecha del pr&oacute;ximo control</label>
              <input type="date" id="proxima" name="proxima_fecha" value="{{ old('proxima_fecha', $proximaSugerida) }}">
            </div>
            <button type="submit" class="boton" id="btn-registrar" @disabled(! $citaElegida)>Guardar en la historia cl&iacute;nica</button>
          </form>
          <p class="nota-regla">Una cita ya atendida no admite un segundo registro cl&iacute;nico:
             la relaci&oacute;n con <code>historias_clinicas</code> es de uno a uno.</p>
        </div>
      </div>

      <div class="bloque" style="margin-top:24px;">
        <h2>Historia cl&iacute;nica registrada</h2>
        <div class="tabla-scroll" style="margin-top:12px;">
          <table class="tabla-app">
            <caption class="oculto-visual">Atenciones registradas en la historia cl&iacute;nica</caption>
            <thead>
              <tr><th scope="col">Mascota</th><th scope="col">Fecha</th><th scope="col">Diagn&oacute;stico</th>
                  <th scope="col">Tratamiento</th><th scope="col">Pr&oacute;ximo control</th></tr>
            </thead>
            <tbody id="cuerpo-historias">
              @forelse ($historias as $historia)
                <tr>
                  <td data-label="Mascota">{{ $mascotas[$historia->mascota_id]->nombre ?? '?' }}</td>
                  <td data-label="Fecha">{{ \Illuminate\Support\Carbon::parse($historia->fecha_atencion)->format('Y-m-d') }}</td>
                  <td data-label="Diagn&oacute;stico">{{ $historia->diagnostico }}</td>
                  <td data-label="Tratamiento">{{ $historia->tratamiento }}</td>
                  <td data-label="Pr&oacute;ximo control">{{ $historia->proxima_fecha ? \Illuminate\Support\Carbon::parse($historia->proxima_fecha)->format('Y-m-d') : '—' }}</td>
                </tr>
              @empty
                <tr><td colspan="5">Sin atenciones registradas.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
@endsection
