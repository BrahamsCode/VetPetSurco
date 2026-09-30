@extends('layouts.app')

@section('titulo', 'Pagar pedido | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: pago del pedido.')

@php
    /* Culqi cobra en centimos enteros, la misma unidad del carrito. */
    $centimos = \App\Services\CarritoService::aCentimos($pedido->monto_total);
    $pagado = $pedido->estado === \App\Enums\EstadoPedido::PAGADO;
    /* Sin llaves de Culqi entra la pasarela simulada (ver AppServiceProvider). */
    $faltaLlavePublica = ! $simulada && $llavePublica === '';
    $intentos = $pedido->pagos->sortByDesc('pago_id');
    /* Desglose fiscal peruano: los precios al publico incluyen IGV 18%. */
    $igv = round((float) $pedido->monto_total - ((float) $pedido->monto_total / 1.18), 2);
    $base = round((float) $pedido->monto_total - $igv, 2);
@endphp

@section('contenido')
    <div class="contenedor">
      <div class="panel-titulo">
        <h1>{{ $pagado ? 'Pedido' : 'Pagar el pedido' }} {{ $pedido->pedido_id }}</h1>
        <p>
          @if ($pagado)
            Pedido pagado. Ahora avanza a ENVIADO cuando la tienda lo despache.
          @else
            El pedido ya reserv&oacute; el stock. Queda confirmarlo con el pago.
          @endif
        </p>
      </div>

      <ol class="pasos-checkout" aria-label="Progreso de la compra">
        <li class="paso paso--hecho"><span class="paso-num" aria-hidden="true">&#10003;</span> Carrito</li>
        <li class="paso-linea" aria-hidden="true"></li>
        <li class="paso {{ $pagado ? 'paso--hecho' : 'paso--activo' }}"><span class="paso-num" aria-hidden="true">2</span> Pago</li>
        <li class="paso-linea" aria-hidden="true"></li>
        <li class="paso {{ $pagado ? 'paso--activo' : '' }}"><span class="paso-num" aria-hidden="true">3</span> Listo</li>
      </ol>

      @include('components.aviso')

      <div class="rejilla rejilla-2">
        {{-- ---------- Columna izquierda: que se esta pagando ---------- --}}
        <div class="bloque">
          <h2>Tu pedido</h2>
          <p>Productos que ya descontaron stock.</p>

          <div class="ticket" style="margin-top:14px;">
            @foreach ($pedido->detalles as $detalle)
              <div class="ticket-linea">
                <div>
                  <p class="ticket-nombre">{{ $detalle->producto->nombre ?? 'Producto '.$detalle->producto_id }}</p>
                  <p class="ticket-detalle">{{ $detalle->cantidad }} &times; S/ {{ number_format((float) $detalle->subtotal / max(1, (int) $detalle->cantidad), 2) }} c/u</p>
                </div>
                <span class="ticket-importe">S/ {{ number_format((float) $detalle->subtotal, 2) }}</span>
              </div>
            @endforeach

            <div class="total-desglose">
              <p class="total-fila"><span>Subtotal (sin IGV)</span><span>S/ {{ number_format($base, 2) }}</span></p>
              <p class="total-fila"><span>IGV 18%</span><span>S/ {{ number_format($igv, 2) }}</span></p>
              <p class="total-fila total-fila--final"><span>Total a pagar</span><span>S/ {{ number_format((float) $pedido->monto_total, 2) }}</span></p>
            </div>
          </div>

          <p class="fiscal-nota">Precios de venta al p&uacute;blico con IGV incluido (D.S. 055-99-EF).
             Recibir&aacute;s tu comprobante por correo al aprobarse el cobro.</p>

          <p class="indicador-etiqueta" style="margin-top:12px;">
            Estado del pedido:
            <span class="estado estado-{{ $pedido->estado->value }}">{{ $pedido->estado->etiqueta() }}</span>
          </p>

          @if ($intentos->isNotEmpty())
            <h2 style="margin-top:22px;">Intentos de cobro</h2>
            <p data-rn="RN-21" data-rn-nota="Todo intento deja rastro">
              Queda registrado tanto el que aprueba como el que no.
            </p>
            <div class="tabla-scroll" style="margin-top:12px;">
              <table class="tabla-app">
                <caption class="oculto-visual">Historial de intentos de cobro</caption>
                <thead>
                  <tr>
                    <th scope="col">Resultado</th>
                    <th scope="col">Tarjeta</th>
                    <th scope="col">Detalle</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($intentos as $intento)
                    <tr>
                      <td data-label="Resultado">
                        <span class="estado estado-{{ $intento->estado === \App\Enums\EstadoPago::APROBADO ? 'ENTREGADO' : 'ANULADO' }}">
                          {{ $intento->estado->etiqueta() }}
                        </span>
                      </td>
                      <td data-label="Tarjeta">
                        @if ($intento->ultimos_cuatro !== null)
                          {{ $intento->marca }} &middot;&middot;&middot;&middot; {{ $intento->ultimos_cuatro }}
                        @else
                          &mdash;
                        @endif
                      </td>
                      <td data-label="Detalle">{{ $intento->mensaje ?? $intento->cargo_culqi }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>

        {{-- ---------- Columna derecha: el cobro ---------- --}}
        <div class="bloque checkout-caja">
          <div class="checkout-cabecera">
            <h2>Pago con tarjeta</h2>
            <span class="checkout-candado">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                <rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
              </svg>
              Pago seguro cifrado
            </span>
          </div>

          @if ($pagado)
            <div class="pago-listo">
              <div class="pago-listo-check" aria-hidden="true">
                <svg viewBox="0 0 48 48" width="38" height="38" fill="none" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M13 25l7.5 7.5L35 16"/>
                </svg>
              </div>
              <h2>&iexcl;Pedido pagado!</h2>
              <p>Gracias por cuidar a tu mascota con nosotros. Tu pedido avanza a ENVIADO cuando la tienda lo despache.</p>
              <p style="margin-top:16px;"><a class="boton" href="{{ route('catalogo') }}">Volver al cat&aacute;logo</a></p>
            </div>

          @elseif ($faltaLlavePublica)
            <p data-rn="RN-21" data-rn-nota="Pasarela mal configurada">
              Hay llave secreta de Culqi pero falta <code>CULQI_LLAVE_PUBLICA</code>
              en el archivo <code>.env</code>. Comp&aacute;ltala y recarga esta p&aacute;gina.
            </p>

          @else
            @if ($simulada)
              <div class="aviso-simulado" style="margin-top:14px;">
                <p>No se cobra dinero real porque no hay llaves de Culqi configuradas.
                   El recorrido es el mismo que en producci&oacute;n: la tarjeta se valida
                   y se convierte en un token aqu&iacute; en el navegador, y el servidor
                   solo recibe ese token.</p>
              </div>
            @else
              <p>Los datos de la tarjeta viajan del navegador directamente a Culqi.
                 El servidor solo recibe un token, nunca el n&uacute;mero.</p>
            @endif

            @if ($simulada)
              {{-- Tarjeta que refleja lo que se escribe abajo y se voltea en el CVV. --}}
              <div class="tarjeta-escena" aria-hidden="true">
                <div class="tarjeta-visual" id="tarjeta">
                  <div class="tarjeta-cara tarjeta-frente">
                    <div class="tarjeta-chip"></div>
                    <p class="tarjeta-numero segmento" id="vista-numero">&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;</p>
                    <div class="tarjeta-pie">
                      <div class="tarjeta-titular">
                        <p class="tarjeta-etiqueta">Titular</p>
                        <p class="tarjeta-dato segmento" id="vista-nombre">TU NOMBRE AQUI</p>
                      </div>
                      <div>
                        <p class="tarjeta-etiqueta">Vence</p>
                        <p class="tarjeta-dato segmento" id="vista-vencimiento">MM/AA</p>
                      </div>
                      {{-- Logos dibujados aqui mismo: sin peticiones a servidores ajenos. --}}
                      <div class="tarjeta-marca" id="vista-marca">
                        <svg class="marca-logo" data-marca="visa" viewBox="0 0 64 22" width="56" height="19">
                          <text x="0" y="17" font-family="Arial, Helvetica, sans-serif" font-size="19"
                                font-weight="700" font-style="italic" letter-spacing=".5" fill="#ffffff">VISA</text>
                        </svg>

                        <svg class="marca-logo" data-marca="mastercard" viewBox="0 0 48 30" width="46" height="29">
                          <circle cx="18" cy="15" r="11" fill="#EB001B"/>
                          <circle cx="30" cy="15" r="11" fill="#F79E1B"/>
                          <path d="M24 6.6a11 11 0 0 0 0 16.8 11 11 0 0 0 0-16.8Z" fill="#FF5F00"/>
                        </svg>

                        <svg class="marca-logo" data-marca="amex" viewBox="0 0 48 30" width="46" height="29">
                          <rect width="48" height="30" rx="4" fill="#006FCF"/>
                          <text x="24" y="19" text-anchor="middle" font-family="Arial, Helvetica, sans-serif"
                                font-size="10" font-weight="700" letter-spacing=".6" fill="#ffffff">AMEX</text>
                        </svg>

                        <svg class="marca-logo" data-marca="diners" viewBox="0 0 48 30" width="46" height="29">
                          <circle cx="24" cy="15" r="13" fill="#0079BE"/>
                          <ellipse cx="24" cy="15" rx="5.5" ry="9.5" fill="#ffffff"/>
                        </svg>
                      </div>
                    </div>
                  </div>

                  <div class="tarjeta-cara tarjeta-reverso">
                    <div class="tarjeta-banda"></div>
                    <div class="tarjeta-firma">
                      <span class="tarjeta-firma-fondo"></span>
                      <span class="tarjeta-cvv" id="vista-cvv">&bull;&bull;&bull;</span>
                    </div>
                    <p class="tarjeta-reverso-nota">C&oacute;digo de seguridad</p>
                  </div>
                </div>
              </div>

              <form method="POST" action="{{ route('pago.procesar', $pedido) }}" id="form-pago" novalidate>
                @csrf
                <input type="hidden" name="token" id="token-pago">

                <div class="campo">
                  <label for="numero">N&uacute;mero de tarjeta</label>
                  <input type="text" id="numero" inputmode="numeric" autocomplete="off"
                         maxlength="23" placeholder="0000 0000 0000 0000"
                         aria-describedby="error-numero">
                  <p class="error-campo" id="error-numero" role="alert" hidden></p>
                </div>

                <div class="campo">
                  <label for="nombre">Nombre del titular</label>
                  <input type="text" id="nombre" autocomplete="off" maxlength="26"
                         placeholder="Como aparece en la tarjeta" aria-describedby="error-nombre">
                  <p class="error-campo" id="error-nombre" role="alert" hidden></p>
                </div>

                <div class="campos-tarjeta">
                  <div class="campo">
                    <label for="vencimiento">Vencimiento</label>
                    <input type="text" id="vencimiento" inputmode="numeric" autocomplete="off"
                           maxlength="5" placeholder="MM/AA" aria-describedby="error-vencimiento">
                    <p class="error-campo" id="error-vencimiento" role="alert" hidden></p>
                  </div>
                  <div class="campo">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" inputmode="numeric" autocomplete="off"
                           maxlength="4" placeholder="123" aria-describedby="error-cvv">
                    <p class="error-campo" id="error-cvv" role="alert" hidden></p>
                  </div>
                </div>

                <p style="margin-top:18px;" data-rn="RN-21" data-rn-nota="Sin aprobacion no hay PAGADO">
                  <button type="submit" class="boton boton-pagar" id="btn-pagar">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                      <rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                    </svg>
                    Pagar S/ {{ number_format((float) $pedido->monto_total, 2) }}
                  </button>
                </p>
              </form>

              <p class="marcas-aceptadas">
                <svg viewBox="0 0 64 22" width="42" height="15" aria-hidden="true"><text x="0" y="17" font-family="Arial, Helvetica, sans-serif" font-size="17" font-weight="700" font-style="italic" fill="#1c2321">VISA</text></svg>
                <svg viewBox="0 0 48 30" width="34" height="22" aria-hidden="true"><circle cx="18" cy="15" r="10" fill="#EB001B"/><circle cx="30" cy="15" r="10" fill="#F79E1B"/></svg>
                Aceptamos Visa, Mastercard, Amex y Diners &middot; tu tarjeta se tokeniza en el navegador
              </p>

              <h3 style="margin-top:24px;">Tarjetas de prueba (demo)</h3>
              <p class="nota-regla">Un clic las carga. Cualquier otro n&uacute;mero inventado no pasa la
                 validaci&oacute;n (algoritmo de Luhn), igual que en una pasarela real.</p>
              <div class="atajos-tarjeta">
                <button type="button" class="atajo-tarjeta" data-resultado="aprueba"
                        data-numero="4111 1111 1111 1111">Visa aprobada</button>
                <button type="button" class="atajo-tarjeta" data-resultado="aprueba"
                        data-numero="5111 1111 1111 1118">Mastercard aprobada</button>
                <button type="button" class="atajo-tarjeta" data-resultado="rechaza"
                        data-numero="4000 0200 0000 0000">Tarjeta rechazada</button>
              </div>

            @else
              <form method="POST" action="{{ route('pago.procesar', $pedido) }}" id="form-pago">
                @csrf
                <input type="hidden" name="token" id="token-pago">
                <p style="margin-top:16px;" data-rn="RN-21" data-rn-nota="Sin aprobacion no hay PAGADO">
                  <button type="button" class="boton boton-pagar" id="btn-pagar">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                      <rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                    </svg>
                    Pagar S/ {{ number_format((float) $pedido->monto_total, 2) }}
                  </button>
                </p>
              </form>
              <p class="nota-regla" id="error-pago" role="alert" hidden></p>
            @endif
          @endif
        </div>
      </div>
    </div>
@endsection

@section('scripts')
  @if (! $pagado && ! $faltaLlavePublica)
    @if ($simulada)
      <script>
        /*
         * Modo simulado. Hace lo mismo que Culqi.js en el navegador: valida la
         * tarjeta y la cambia por un token. El numero completo nunca se envia;
         * el token solo lleva la marca y los cuatro ultimos digitos.
         */
        (function () {
          const tarjeta = document.getElementById('tarjeta');
          const form = document.getElementById('form-pago');
          const campoNumero = document.getElementById('numero');
          const campoNombre = document.getElementById('nombre');
          const campoVence = document.getElementById('vencimiento');
          const campoCvv = document.getElementById('cvv');

          const soloDigitos = (texto) => texto.replace(/\D/g, '');

          // Rangos reales de cada emisor, no solo el primer digito.
          const marcaDe = (numero) => {
            if (/^4/.test(numero)) return 'visa';
            if (/^(5[1-5]|2[2-7])/.test(numero)) return 'mastercard';
            if (/^3[47]/.test(numero)) return 'amex';
            if (/^3(0[0-5]|[68])/.test(numero)) return 'diners';
            return '';
          };

          // Amex son 15 digitos agrupados 4-6-5 y su codigo tiene 4 cifras.
          const formatoDe = (marca) => marca === 'amex'
            ? { grupos: [4, 6, 5], largo: 15, cvv: 4 }
            : { grupos: [4, 4, 4, 4], largo: 16, cvv: 3 };

          const agrupar = (numero, grupos) => {
            const partes = [];
            let desde = 0;

            grupos.forEach(function (tamano) {
              if (desde >= numero.length) return;
              partes.push(numero.substr(desde, tamano));
              desde += tamano;
            });

            return partes.join(' ');
          };

          /*
           * Algoritmo de Luhn: el digito verificador que llevan todas las
           * tarjetas reales. Es lo que impide que un numero inventado al azar
           * se acepte como valido.
           */
          const cumpleLuhn = (numero) => {
            let suma = 0;
            let alternar = false;

            for (let i = numero.length - 1; i >= 0; i--) {
              let digito = parseInt(numero.charAt(i), 10);

              if (alternar) {
                digito *= 2;
                if (digito > 9) digito -= 9;
              }

              suma += digito;
              alternar = !alternar;
            }

            return suma % 10 === 0;
          };

          const mostrarError = (id, mensaje, campo) => {
            const aviso = document.getElementById(id);
            aviso.textContent = mensaje;
            aviso.hidden = mensaje === '';
            campo.classList.toggle('campo-invalido', mensaje !== '');
          };

          // --- Pintado en vivo de la tarjeta ---
          const logos = document.querySelectorAll('.marca-logo');
          const vistaNumero = document.getElementById('vista-numero');
          const vistaNombre = document.getElementById('vista-nombre');
          const vistaVence = document.getElementById('vista-vencimiento');
          const vistaCvv = document.getElementById('vista-cvv');

          const refrescarTarjeta = () => {
            const numero = soloDigitos(campoNumero.value);
            const marca = marcaDe(numero);
            const formato = formatoDe(marca);

            // Los huecos que faltan se rellenan con puntos, como en una tarjeta real.
            const relleno = (numero + '•'.repeat(formato.largo)).slice(0, formato.largo);

            vistaNumero.textContent = agrupar(relleno, formato.grupos);
            vistaNombre.textContent = campoNombre.value.trim() || 'TU NOMBRE AQUI';
            vistaVence.textContent = campoVence.value || 'MM/AA';
            vistaCvv.textContent = campoCvv.value || '•'.repeat(formato.cvv);

            logos.forEach(function (logo) {
              logo.classList.toggle('marca-activa', logo.dataset.marca === marca);
            });
          };

          campoNumero.addEventListener('input', function () {
            const formato = formatoDe(marcaDe(soloDigitos(this.value)));
            this.value = agrupar(soloDigitos(this.value).slice(0, formato.largo), formato.grupos);
            mostrarError('error-numero', '', this);
            refrescarTarjeta();
          });

          campoNombre.addEventListener('input', function () {
            mostrarError('error-nombre', '', this);
            refrescarTarjeta();
          });

          campoVence.addEventListener('input', function () {
            const digitos = soloDigitos(this.value).slice(0, 4);
            this.value = digitos.length > 2
              ? digitos.slice(0, 2) + '/' + digitos.slice(2)
              : digitos;
            mostrarError('error-vencimiento', '', this);
            refrescarTarjeta();
          });

          campoCvv.addEventListener('input', function () {
            const formato = formatoDe(marcaDe(soloDigitos(campoNumero.value)));
            this.value = soloDigitos(this.value).slice(0, formato.cvv);
            mostrarError('error-cvv', '', this);
            refrescarTarjeta();
          });

          /*
           * La tarjeta acompana al campo que se esta escribiendo: resalta el
           * trozo correspondiente y enseña el reverso cuando toca el CVV.
           */
          const segmentos = {
            numero: vistaNumero,
            nombre: vistaNombre,
            vencimiento: vistaVence,
            cvv: vistaCvv,
          };

          Object.keys(segmentos).forEach(function (clave) {
            const campo = document.getElementById(clave);

            campo.addEventListener('focus', function () {
              Object.values(segmentos).forEach(function (segmento) {
                segmento.classList.remove('segmento-activo');
              });
              segmentos[clave].classList.add('segmento-activo');
              tarjeta.classList.toggle('tarjeta-volteada', clave === 'cvv');
            });

            campo.addEventListener('blur', function () {
              segmentos[clave].classList.remove('segmento-activo');
              if (clave === 'cvv') tarjeta.classList.remove('tarjeta-volteada');
            });
          });

          // --- Atajos con las tarjetas de prueba ---
          document.querySelectorAll('.atajo-tarjeta').forEach(function (boton) {
            boton.addEventListener('click', function () {
              campoNumero.value = boton.dataset.numero;
              campoNombre.value = campoNombre.value || 'ANA QUISPE';
              campoVence.value = '09/30';
              campoCvv.value = '123';
              ['numero', 'nombre', 'vencimiento', 'cvv'].forEach(function (clave) {
                mostrarError('error-' + clave, '', document.getElementById(clave));
              });
              refrescarTarjeta();
            });
          });

          // --- Validacion y generacion del token ---
          form.addEventListener('submit', function (e) {
            const numero = soloDigitos(campoNumero.value);
            const vence = soloDigitos(campoVence.value);
            const cvv = campoCvv.value;
            const marca = marcaDe(numero);
            const formato = formatoDe(marca);
            let hayError = false;

            if (marca === '') {
              mostrarError('error-numero', 'No reconocemos esa marca de tarjeta.', campoNumero);
              hayError = true;
            } else if (numero.length !== formato.largo || !cumpleLuhn(numero)) {
              mostrarError('error-numero', 'Ese numero de tarjeta no es valido.', campoNumero);
              hayError = true;
            }

            if (campoNombre.value.trim().length < 3) {
              mostrarError('error-nombre', 'Escribe el nombre que figura en la tarjeta.', campoNombre);
              hayError = true;
            }

            const mes = parseInt(vence.slice(0, 2), 10);
            const anio = parseInt(vence.slice(2), 10);
            const ahora = new Date();
            const anioActual = ahora.getFullYear() % 100;
            const mesActual = ahora.getMonth() + 1;

            if (vence.length !== 4 || !(mes >= 1 && mes <= 12)) {
              mostrarError('error-vencimiento', 'Usa el formato MM/AA.', campoVence);
              hayError = true;
            } else if (anio < anioActual || (anio === anioActual && mes < mesActual)) {
              mostrarError('error-vencimiento', 'La tarjeta esta vencida.', campoVence);
              hayError = true;
            }

            if (cvv.length !== formato.cvv) {
              mostrarError('error-cvv', 'El codigo debe tener ' + formato.cvv + ' cifras.', campoCvv);
              hayError = true;
            }

            if (hayError) {
              e.preventDefault();
              return;
            }

            document.getElementById('token-pago').value =
              'tkn_sim_' + (marcaDe(numero) || 'desconocida') + '_' + numero.slice(-4);
          });

          refrescarTarjeta();
        })();
      </script>
    @else
      <script src="https://js.culqi.com/checkout-js"></script>
      <script>
        const checkout = new CulqiCheckout(@json($llavePublica), {
          settings: {
            title: 'VetPet Surco',
            currency: 'PEN',
            amount: {{ $centimos }},
          },
          options: {
            lang: 'es',
            modal: true,
            installments: false,
            paymentMethods: { tarjeta: true },
          },
        });

        // Culqi avisa por aqui tanto el token como el error.
        checkout.culqi = function () {
          if (checkout.token) {
            document.getElementById('token-pago').value = checkout.token.id;
            document.getElementById('form-pago').submit();
            return;
          }

          if (checkout.error) {
            const aviso = document.getElementById('error-pago');
            aviso.textContent = checkout.error.user_message || 'No se pudo generar el token de la tarjeta.';
            aviso.hidden = false;
          }
        };

        document.getElementById('btn-pagar').addEventListener('click', function (e) {
          e.preventDefault();
          checkout.open();
        });
      </script>
    @endif
  @endif
@endsection
