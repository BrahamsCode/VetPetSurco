@extends('layouts.app')

@section('titulo', 'Mis pedidos | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: seguimiento de mis pedidos.')

@section('contenido')
    <div class="contenedor">
      <div class="panel-titulo">
        <h1>Mis pedidos</h1>
        <p>En qu&eacute; va cada compra, c&oacute;mo la recibes y qu&eacute; falta.</p>
      </div>

      @include('components.aviso')

      @if ($pedidos->isEmpty())
        <div class="bloque">
          <div class="clinica-vacio" style="margin-top:0;">
            <p class="clinica-vacio-titulo">Todav&iacute;a no tienes pedidos</p>
            <p>Cuando compres algo en el cat&aacute;logo, aqu&iacute; ver&aacute;s c&oacute;mo avanza hasta tu puerta.</p>
            <p style="margin-top:12px !important;"><a class="boton" href="{{ route('catalogo') }}">Ir al cat&aacute;logo</a></p>
          </div>
        </div>
      @else
        <ul class="pedidos-lista">
          @foreach ($pedidos as $pedido)
            @php
                $estado = $pedido->estadoActual();
                $modalidad = $pedido->modalidad();
                $esDelivery = $modalidad === \App\Enums\ModalidadEntrega::DELIVERY;
                $pagoAprobado = $pedido->pagos->first(fn ($p) => $p->estado === \App\Enums\EstadoPago::APROBADO);

                // Pasos de la entrega; el paso 2 cambia de nombre segun la modalidad.
                $orden = ['PAGADO' => 1, 'ENVIADO' => 2, 'ENTREGADO' => 3];
                $alcanzado = $orden[$estado->value] ?? 0;
                $pasos = [
                    ['Pagado', $pagoAprobado?->fecha_pago],
                    [$esDelivery ? 'En camino' : 'Listo para recoger', $pedido->enviado_en],
                    ['Entregado', $pedido->entregado_en],
                ];
            @endphp
            <li class="bloque pedido">
              <div class="pedido-cabecera">
                <div>
                  <p class="pedido-numero">Pedido #{{ $pedido->pedido_id }}</p>
                  <p class="pedido-meta">
                    {{ $pedido->fecha_pedido->format('d/m/Y H:i') }}
                    @if ($pedido->tipo_origen === \App\Enums\TipoOrigen::SUSCRIPCION) &middot; Despacho de tu plan mensual @endif
                  </p>
                </div>
                <div class="pedido-total">
                  <span class="estado estado-{{ $estado->value }}">{{ $pedido->etiquetaEstado() }}</span>
                  <p>S/ {{ number_format((float) $pedido->totalACobrar(), 2) }}</p>
                </div>
              </div>

              @if ($estado === \App\Enums\EstadoPedido::ANULADO)
                <p class="pedido-aviso pedido-aviso-gris">
                  Anulado el {{ $pedido->anulado_en?->format('d/m/Y H:i') ?? '—' }}.
                  {{ $pedido->motivo_anulacion }} Los productos volvieron a la tienda.
                </p>
              @elseif ($estado === \App\Enums\EstadoPedido::PENDIENTE)
                <div class="pedido-aviso">
                  <p>
                    <strong>Falta pagarlo.</strong> Tus productos est&aacute;n separados hasta el
                    <strong>{{ $pedido->vencePagoEl()->format('d/m/Y \a \l\a\s H:i') }}</strong>;
                    si no se paga, el pedido se anula solo.
                  </p>
                  <div class="pedido-acciones">
                    <a class="boton-mini boton-mini-primario" href="{{ route('pago', $pedido) }}">Pagar ahora</a>
                    <form method="POST" action="{{ route('pedidos.anular', $pedido) }}"
                          data-confirmar-titulo="¿Anular el pedido #{{ $pedido->pedido_id }}?"
                          data-confirmar="Los productos vuelven a la tienda y el pedido ya no se podrá pagar. Esto no se puede deshacer."
                          data-confirmar-boton="Sí, anular pedido">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="boton-mini boton-mini-peligro">Anular pedido</button>
                    </form>
                  </div>
                </div>
              @else
                <ol class="avance" aria-label="Avance del pedido">
                  @foreach ($pasos as $indice => [$nombrePaso, $cuando])
                    @php $hecho = $indice + 1 <= $alcanzado; @endphp
                    <li class="avance-paso{{ $hecho ? ' avance-hecho' : '' }}{{ $indice + 1 === $alcanzado ? ' avance-actual' : '' }}"
                        @if ($indice + 1 === $alcanzado) aria-current="step" @endif>
                      <span class="avance-punto" aria-hidden="true"></span>
                      <span class="avance-nombre">{{ $nombrePaso }}</span>
                      <span class="avance-fecha">{{ $hecho && $cuando ? $cuando->format('d/m H:i') : ($hecho ? 'Listo' : 'Pendiente') }}</span>
                    </li>
                  @endforeach
                </ol>
              @endif

              <dl class="pedido-datos">
                <div>
                  <dt>Entrega</dt>
                  <dd>
                    {{ $modalidad->etiqueta() }}
                    @if ($esDelivery)
                      &middot; {{ $pedido->direccion_entrega }}
                    @else
                      &middot; Av. Velasco Astete 1245, Surco (lun.&ndash;vie. 9:00&ndash;20:00, s&aacute;b. 9:00&ndash;14:00)
                    @endif
                  </dd>
                </div>
                <div>
                  <dt>Productos</dt>
                  <dd>
                    @foreach ($pedido->detalles as $detalle)
                      {{ $detalle->cantidad }} &times; {{ $detalle->producto->nombre ?? 'Producto' }}@if (! $loop->last), @endif
                    @endforeach
                  </dd>
                </div>
                @if ((float) $pedido->costo_envio > 0)
                  <div><dt>Env&iacute;o</dt><dd>S/ {{ number_format((float) $pedido->costo_envio, 2) }}</dd></div>
                @endif
              </dl>
            </li>
          @endforeach
        </ul>

        {{ $pedidos->links('components.paginacion') }}
      @endif
    </div>
@endsection
