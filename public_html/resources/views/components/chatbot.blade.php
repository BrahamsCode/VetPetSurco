{{--
  Pelusa, el asistente de ayuda de VetPet Connect (Nivel 2).
  Menu guiado + campo de texto libre: las preguntas van al backend, que
  entiende la intencion y responde con datos reales de la cuenta (solo los
  del propio usuario). Sin JavaScript se muestra una ayuda estatica.
  Solo se muestra a los clientes, que son quienes compran y reservan.
--}}
@php $rolAyuda = auth()->check() ? (auth()->user()->rol instanceof \App\Enums\Rol ? auth()->user()->rol->value : (string) auth()->user()->rol) : null; @endphp

@if ($rolAyuda === 'CLIENTE')
  <div class="chat-ayudante"
       data-guias="{{ route('ayuda.guias') }}"
       data-consultar="{{ route('ayuda.consultar') }}"
       data-csrf="{{ csrf_token() }}">

    <section class="chat-panel" id="chat-panel" role="dialog" aria-modal="true" aria-labelledby="chat-titulo" hidden>
      <header class="chat-cabecera">
        <span class="chat-avatar" aria-hidden="true">
          <svg viewBox="0 0 64 64" width="24" height="24">
            <ellipse cx="32" cy="45" rx="15" ry="12"/>
            <ellipse cx="13" cy="31" rx="6.4" ry="8.4"/>
            <ellipse cx="25" cy="18" rx="6.4" ry="8.8"/>
            <ellipse cx="41" cy="18" rx="6.4" ry="8.8"/>
            <ellipse cx="53" cy="31" rx="6.4" ry="8.4"/>
          </svg>
        </span>
        <div class="chat-quien">
          <strong id="chat-titulo">Pelusa</strong>
          <small>Asistente VetPet &middot; en l&iacute;nea</small>
        </div>
        <button type="button" class="chat-cerrar" id="chat-cerrar" aria-label="Cerrar el asistente">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18"/>
          </svg>
        </button>
      </header>

      <div class="chat-mensajes" id="chat-mensajes" role="log" aria-live="polite"></div>
      <div class="chat-opciones" id="chat-opciones"></div>

      <form class="chat-form" id="chat-form">
        <label class="oculto-visual" for="chat-texto">Escribe tu pregunta a Pelusa</label>
        <input type="text" id="chat-texto" name="mensaje" maxlength="300" autocomplete="off"
               placeholder="Escribe tu pregunta&hellip;">
        <button type="submit" class="chat-enviar" aria-label="Enviar la pregunta">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>
      </form>
    </section>

    <button type="button" class="chat-fab" id="chat-fab"
            aria-expanded="false" aria-controls="chat-panel"
            aria-label="Abrir a Pelusa, el asistente de ayuda">
      <svg viewBox="0 0 64 64" width="26" height="26" aria-hidden="true">
        <ellipse cx="32" cy="45" rx="15" ry="12"/>
        <ellipse cx="13" cy="31" rx="6.4" ry="8.4"/>
        <ellipse cx="25" cy="18" rx="6.4" ry="8.8"/>
        <ellipse cx="41" cy="18" rx="6.4" ry="8.8"/>
        <ellipse cx="53" cy="31" rx="6.4" ry="8.4"/>
      </svg>
      <span class="chat-globo" id="chat-globo" aria-hidden="true">&iquest;Necesitas ayuda?</span>
    </button>

    {{-- Sin JavaScript el chat no puede abrirse: se ofrece ayuda directa. --}}
    <noscript>
      <style>
        .chat-ayudante .chat-fab, .chat-ayudante .chat-globo { display: none !important; }
      </style>
      <aside class="chat-sin-js" aria-label="Ayuda de VetPet Surco">
        <strong>&iquest;Necesitas ayuda?</strong>
        <p>Ll&aacute;manos al <a href="tel:+51987654321">987 654 321</a> o escr&iacute;benos a
           <a href="mailto:contacto@vetpetsurco.pe">contacto@vetpetsurco.pe</a>.</p>
        <p><a href="{{ route('contacto') }}">Formulario de contacto</a> &middot;
           Lun a vie 9:00-20:00, s&aacute;b 9:00-14:00.</p>
      </aside>
    </noscript>
  </div>

  <script src="{{ asset('js/pelusa.js') }}?v={{ filemtime(public_path('js/pelusa.js')) }}" defer></script>
@endif
