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
        <div style="overflow-x:auto;margin-top:14px;">
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
                  <td>{{ $suscripcion->plan }}</td>
                  <td>{{ $porMascota[$suscripcion->mascota_id]->nombre ?? '?' }}</td>
                  <td>{{ $productos[$suscripcion->producto_id]->nombre ?? '?' }}</td>
                  <td>S/ {{ number_format((float) $suscripcion->monto_mensual, 2) }}</td>
                  <td>{{ $despacho->format('Y-m-d') }} <small>(en {{ $dias }} d&iacute;as, cada {{ $suscripcion->frecuencia_dias }})</small></td>
                  <td><span class="estado estado-{{ $estado }}">{{ $estado }}</span></td>
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
                <tr><td colspan="7">No tienes suscripciones activas.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
@endsection
