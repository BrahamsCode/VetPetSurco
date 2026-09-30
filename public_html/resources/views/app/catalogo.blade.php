@extends('layouts.app')

@section('titulo', 'Catálogo | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: catálogo.')

@section('contenido')
    <div class="contenedor">
      <div class="panel-titulo">
        <h1>Cat&aacute;logo</h1>
        <p>Precios en soles con IGV 18% incluido (D.S. 055-99-EF). El stock que ves es el stock real del inventario.</p>
      </div>

      <div class="bloque" style="margin-bottom:22px;">
        <form method="GET" action="{{ route('catalogo') }}">
          <div class="columnas" style="gap:18px;align-items:flex-end;">
            <div class="columna" style="flex:1 1 260px;">
              <label for="filtro" style="font-weight:700;font-size:14px;color:var(--verde-oscuro);display:block;margin-bottom:6px;">Categor&iacute;a</label>
              <select id="filtro" name="categoria" class="horario" style="width:100%;padding:11px 14px;">
                <option value="" @selected($categoria === '')>Todas las categor&iacute;as</option>
                <option value="ALIMENTO" @selected($categoria === 'ALIMENTO')>Alimento</option>
                <option value="ACCESORIO" @selected($categoria === 'ACCESORIO')>Accesorios</option>
                <option value="MEDICAMENTO" @selected($categoria === 'MEDICAMENTO')>Medicamentos</option>
                <option value="ARENA" @selected($categoria === 'ARENA')>Arena</option>
              </select>
            </div>
            <div class="columna" style="flex:0 0 auto;">
              <button type="submit" class="boton-mini">Filtrar</button>
            </div>
            <div class="columna" style="flex:1 1 220px;">
              <p style="margin:0;font-size:14px;color:var(--gris);">
                En el carrito: <strong id="contador-carrito" style="color:var(--verde-oscuro);">{{ $unidades }}</strong> unidades.
                <a href="{{ route('carrito') }}">Ver carrito</a>
              </p>
            </div>
          </div>
        </form>
      </div>

      @include('components.aviso')

      <div class="rejilla rejilla-3" id="lista-productos">
        @forelse ($productos as $producto)
          @php
              /* Las reglas valen para todas las tarjetas; se marcan solo en la
                 primera para que las capturas anotadas queden legibles. */
              $marcar = $loop->first;
              $semaforo = $semaforos[$producto->producto_id] ?? 'VERDE';
              $agotado = (int) $producto->stock_actual === 0;
          @endphp
          <article class="tarjeta producto">
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
