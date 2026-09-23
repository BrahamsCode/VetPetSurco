{{--
  Tabla etiqueta/valor reutilizable de los correos.
  Recibe $filas: lista de pares [etiqueta, valor].
--}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border:1px solid #d9e5df;border-radius:10px;">
  @foreach ($filas as $indice => $fila)
    <tr>
      <td style="padding:10px 14px;{{ $indice > 0 ? 'border-top:1px solid #d9e5df;' : '' }}color:#5d6b66;">{{ $fila[0] }}</td>
      <td style="padding:10px 14px;{{ $indice > 0 ? 'border-top:1px solid #d9e5df;' : '' }}font-weight:700;">{{ $fila[1] }}</td>
    </tr>
  @endforeach
</table>
