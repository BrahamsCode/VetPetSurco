{{--
  Un pedido en el tablero del admin, con la accion que toca en su etapa.
  Recibe: $pedido (con cliente y detalles.producto cargados).
--}}
@php
    $estado = $pedido->estadoActual();
    $esDelivery = $pedido->modalidad() === \App\Enums\ModalidadEntrega::DELIVERY;
    $productos = $pedido->detalles
        ->map(fn ($d) => $d->cantidad.' × '.($d->producto->nombre ?? 'Producto'))
        ->implode(', ');
@endphp
<li class="tarjeta-pedido">
  <div class="tarjeta-pedido-cabecera">
    <span class="tarjeta-pedido-numero">#{{ $pedido->pedido_id }}</span>
    <span class="entrega-chip entrega-chip-{{ $esDelivery ? 'delivery' : 'recojo' }}">{{ $esDelivery ? 'Delivery' : 'Recojo' }}</span>
    @if ($pedido->tipo_origen === \App\Enums\TipoOrigen::SUSCRIPCION)
      <span class="origen-SUSCRIPCION" title="Despacho de suscripción">Plan</span>
    @endif
    <span class="tarjeta-pedido-total">S/ {{ number_format((float) $pedido->totalACobrar(), 2) }}</span>
  </div>
  <p class="tarjeta-pedido-cliente">
    {{ $pedido->cliente->nombre ?? '?' }}
    @if ($pedido->cliente?->telefono) &middot; <a href="tel:{{ $pedido->cliente->telefono }}">{{ $pedido->cliente->telefono }}</a>@endif
  </p>
  @if ($esDelivery)
    <p class="tarjeta-pedido-direccion">{{ $pedido->direccion_entrega }}</p>
  @endif
  <p class="tarjeta-pedido-productos">{{ $productos }}</p>

  <div class="tarjeta-pedido-pie">
    @if ($estado === \App\Enums\EstadoPedido::PAGADO)
      <form method="POST" action="{{ route('admin.pedidos.avanzar', $pedido->pedido_id) }}">
        @csrf
        @method('PATCH')
        <button type="submit" class="boton-mini boton-mini-primario">{{ $esDelivery ? 'Enviar a domicilio' : 'Listo para recoger' }}</button>
      </form>
    @elseif ($estado === \App\Enums\EstadoPedido::ENVIADO)
      <span class="tarjeta-pedido-hora">{{ $esDelivery ? 'Salió' : 'Listo' }} {{ $pedido->enviado_en?->format('d/m H:i') }}</span>
      <form method="POST" action="{{ route('admin.pedidos.avanzar', $pedido->pedido_id) }}">
        @csrf
        @method('PATCH')
        <button type="submit" class="boton-mini boton-mini-primario">Marcar entregado</button>
      </form>
    @elseif ($estado === \App\Enums\EstadoPedido::ENTREGADO)
      <span class="tarjeta-pedido-hora">Entregado {{ $pedido->entregado_en?->format('H:i') }}</span>
    @elseif ($estado === \App\Enums\EstadoPedido::PENDIENTE)
      <span class="tarjeta-pedido-hora">Vence {{ $pedido->vencePagoEl()->format('d/m H:i') }}</span>
      <form method="POST" action="{{ route('admin.pedidos.anular', $pedido->pedido_id) }}"
            data-confirmar-titulo="¿Anular el pedido #{{ $pedido->pedido_id }}?"
            data-confirmar="Es de {{ $pedido->cliente->nombre ?? 'un cliente' }} y todavía no se paga. El stock vuelve al inventario y el pedido ya no se podrá cobrar."
            data-confirmar-boton="Sí, anular">
        @csrf
        @method('PATCH')
        <button type="submit" class="boton-mini boton-mini-peligro">Anular</button>
      </form>
    @endif
  </div>
</li>
