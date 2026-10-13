<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($paginas as $pagina)
  <url>
    <loc>{{ $pagina['ruta'] }}</loc>
    @isset($pagina['fecha'])<lastmod>{{ $pagina['fecha'] }}</lastmod>@endisset
    <changefreq>{{ $pagina['frecuencia'] }}</changefreq>
    <priority>{{ $pagina['prioridad'] }}</priority>
  </url>
@endforeach
</urlset>
