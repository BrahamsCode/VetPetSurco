@extends('layouts.app')

@section('titulo', 'Catálogo | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: catálogo.')

@section('contenido')
    <div class="contenedor">
      <div class="panel-titulo panel-titulo-catalogo">
        <h1>Cat&aacute;logo</h1>
        <p>Precios con IGV incluido. El stock que ves es el real del inventario.</p>
      </div>

      {{-- Filtro por categoria como chips: un toque y filtra; en el celular la
           fila se desliza con el dedo en vez de ocupar un bloque entero. --}}
      <nav class="categorias" aria-label="Filtrar por categor&iacute;a">
        @foreach (['' => 'Todo', 'ALIMENTO' => 'Alimento', 'ACCESORIO' => 'Accesorios', 'MEDICAMENTO' => 'Medicamentos', 'ARENA' => 'Arena'] as $valor => $texto)
          <a class="chip" href="{{ route('catalogo', $valor !== '' ? ['categoria' => $valor] : []) }}"
             @if ($categoria === $valor) aria-current="page" @endif>{{ $texto }}</a>
        @endforeach
      </nav>

      @include('components.aviso')

      <div class="rejilla rejilla-3 catalogo-rejilla" id="lista-productos">
        @forelse ($productos as $producto)
          @php
              /* Las reglas valen para todas las tarjetas; se marcan solo en la
                 primera para que las capturas anotadas queden legibles. */
              $marcar = $loop->first;
              $semaforo = $semaforos[$producto->producto_id] ?? 'VERDE';
              $agotado = (int) $producto->stock_actual === 0;
          @endphp
          <article class="tarjeta producto">
            <div class="producto-imagen{{ $producto->tieneFoto() ? '' : ' producto-imagen-icono' }}{{ $agotado ? ' producto-imagen-agotado' : '' }}">
              <img src="{{ $producto->urlImagen() }}" alt="{{ $producto->nombre }}"
                   width="800" height="600" @if ($loop->index > 2) loading="lazy" @endif decoding="async">
              @if ($agotado)
                <span class="producto-cinta">Agotado</span>
              @endif
            </div>
            <p class="producto-sku"@if ($marcar) data-rn="RN-05" data-rn-nota="SKU irrepetible"@endif>{{ $producto->codigo_sku }}</p>
            <h2 class="producto-nombre">{{ $producto->nombre }}</h2>
            <p class="producto-precio"@if ($marcar) data-rn="RN-06" data-rn-nota="Precio &gt; 0"@endif>S/ {{ number_format((float) $producto->precio, 2) }}</p>
            <p class="producto-igv">Incluye IGV 18%</p>
            <p class="semaforo semaforo-{{ $semaforo }}">{{ $producto->stock_actual }} en stock</p>
            <form class="producto-pie" method="POST" action="{{ route('carrito.agregar') }}">
              @csrf
              <input type="hidden" name="producto_id" value="{{ $producto->producto_id }}">
              <label class="oculto-visual" for="cant-{{ $producto->producto_id }}">Cantidad</label>
              <input type="number" id="cant-{{ $producto->producto_id }}" name="cantidad" value="1" min="1" step="1"@if ($marcar) data-rn="RN-10" data-rn-nota="Cantidad &gt; 0"@endif @disabled($agotado)>
              <button type="submit" class="boton"@if ($marcar) data-rn="RN-09" data-rn-nota="Acumula, no duplica"@endif @disabled($agotado)>{{ $agotado ? 'Sin stock' : 'Agregar' }}</button>
            </form>
          </article>
        @empty
          <p class="nota-regla">No hay productos activos en esta categor&iacute;a.</p>
        @endforelse
      </div>
    </div>
@endsection
