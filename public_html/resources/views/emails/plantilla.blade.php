{{--
  Plantilla base de los correos de VetPet Connect.
  Cada tipo de correo la extiende y define sus secciones 'titulo' y
  'contenido'. El diseno (etiqueta y color de cabecera) sale de
  config/correos.php segun el tipo. Estilos en linea: los clientes de
  correo no aplican hojas de estilo externas.
--}}
@php
    $estilo = config('correos.tipos.'.$tipo, []);
    $etiqueta = $estilo['etiqueta'] ?? 'VetPet Surco';
    $color = $estilo['color'] ?? '#14524a';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo', $etiqueta.' | VetPet Surco')</title>
</head>
<body style="margin:0;padding:0;background-color:#eef5f1;font-family:Helvetica,Arial,sans-serif;color:#1c2321;">

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef5f1;padding:24px 12px;">
    <tr><td align="center">

      <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;">

        {{-- Cabecera de marca: el color identifica el tipo de correo. --}}
        <tr>
          <td style="background-color:{{ $color }};padding:26px 30px;color:#ffffff;">
            <p style="margin:0;font-size:20px;font-weight:700;">VetPet Surco</p>
            <p style="margin:4px 0 0 0;font-size:13px;color:#eef5f1;">{{ $etiqueta }}</p>
          </td>
        </tr>

        <tr><td style="padding:28px 30px 8px 30px;">
          <h1 style="margin:0 0 6px 0;font-size:22px;color:#14524a;">@yield('titulo')</h1>
          <div style="font-size:14px;color:#5d6b66;line-height:1.6;">
            @yield('contenido')
          </div>
        </td></tr>

        <tr><td style="padding:10px 30px 28px 30px;">
          <p style="margin:0;font-size:12px;color:#5d6b66;">
            VetPet Surco E.I.R.L. &middot; Av. Velasco Astete 1245, Santiago de Surco, Lima.
            (01) 445-8820 / 987 654 321 &middot; contacto@vetpetsurco.pe
          </p>
          <p style="margin:10px 0 0 0;font-size:11px;color:#8a978f;">
            Proyecto acad&eacute;mico del Curso de E-business (Equipo 4, 2026).
            Empresa ficticia con fines educativos.
          </p>
        </td></tr>

      </table>

    </td></tr>
  </table>

</body>
</html>
