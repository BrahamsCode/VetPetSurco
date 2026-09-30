{{--
  Paginacion con el estilo de la plataforma (la de Laravel trae clases de
  Tailwind, que aqui no existen). Uso: $paginas->links('components.paginacion')
--}}
@if ($paginator->hasPages())
  <nav class="paginacion" aria-label="Paginas del listado">
    @if ($paginator->onFirstPage())
      <span class="boton-mini paginacion-inactivo" aria-disabled="true">&larr; Anterior</span>
    @else
      <a class="boton-mini" href="{{ $paginator->previousPageUrl() }}" rel="prev">&larr; Anterior</a>
    @endif

    <span class="paginacion-estado">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>

    @if ($paginator->hasMorePages())
      <a class="boton-mini" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente &rarr;</a>
    @else
      <span class="boton-mini paginacion-inactivo" aria-disabled="true">Siguiente &rarr;</span>
    @endif
  </nav>
@endif
