/* =====================================================================
   VetPet Connect - Sesion, navegacion por rol y utilidades de interfaz
   ===================================================================== */
(function (global) {
  "use strict";

  var CLAVE_SESION = "vetpet.connect.sesion.v1";

  // RN-01: cada rol accede unicamente a los modulos que le corresponden.
  var MODULOS = {
    CLIENTE:     [["catalogo.html", "Catálogo"], ["carrito.html", "Carrito"],
                  ["citas.html", "Reservar cita"], ["mascotas.html", "Mis mascotas"]],
    VETERINARIO: [["clinica.html", "Ficha clínica"]],
    ADMIN:       [["admin.html", "Dashboard"]]
  };

  var Sesion = {
    abrir: function (usuario) {
      try { sessionStorage.setItem(CLAVE_SESION, JSON.stringify(usuario)); } catch (e) {}
      Sesion._u = usuario;
    },
    actual: function () {
      if (Sesion._u) { return Sesion._u; }
      try {
        var s = sessionStorage.getItem(CLAVE_SESION);
        Sesion._u = s ? JSON.parse(s) : null;
      } catch (e) { Sesion._u = null; }
      return Sesion._u;
    },
    cerrar: function () {
      Sesion._u = null;
      try { sessionStorage.removeItem(CLAVE_SESION); } catch (e) {}
      location.href = "index.html";
    },
    // Guarda de acceso: si el rol no corresponde, no se pinta la pantalla.
    exigir: function (rolesPermitidos) {
      var u = Sesion.actual();
      if (!u) { location.href = "index.html"; return null; }
      if (rolesPermitidos && rolesPermitidos.indexOf(u.rol) === -1) {
        location.href = "index.html"; return null;
      }
      return u;
    }
  };

  // Encabezado comun de la aplicacion, con el rol de la sesion visible.
  function pintarCabecera(paginaActual) {
    var u = Sesion.actual();
    var cab = document.querySelector("[data-cabecera]");
    if (!cab || !u) { return; }

    var enlaces = (MODULOS[u.rol] || []).map(function (m) {
      var activo = m[0] === paginaActual ? ' aria-current="page"' : "";
      return '<li><a href="' + m[0] + '"' + activo + ">" + m[1] + "</a></li>";
    }).join("");

    cab.innerHTML =
      '<div class="contenedor barra-superior">' +
        '<a class="logo" href="../index.html">' +
          '<img src="../img/logo.svg" alt="VetPet Surco" width="38" height="38">' +
          '<span class="logo-texto">VetPet <span>Connect</span></span>' +
        '</a>' +
        '<nav aria-label="Módulos de la plataforma"><ul class="menu">' + enlaces + '</ul></nav>' +
        '<div class="sesion" data-rn="RN-01" data-rn-nota="Rol excluyente">' +
          '<span class="sesion-nombre">' + escapar(u.nombre) + '</span>' +
          '<span class="pastilla-rol">' + u.rol + '</span>' +
          '<button type="button" class="boton-mini" data-salir>Salir</button>' +
        '</div>' +
      '</div>';

    cab.querySelector("[data-salir]").addEventListener("click", Sesion.cerrar);
  }

  function escapar(t) {
    var d = document.createElement("div");
    d.textContent = t == null ? "" : String(t);
    return d.innerHTML;
  }

  function soles(n) { return "S/ " + Number(n).toFixed(2); }

  // Aviso de resultado de una regla: verde si se cumplio, rojo si la bloqueo.
  function avisar(contenedor, resultado) {
    if (!contenedor) { return; }
    var malo = resultado && resultado.ok === false;
    contenedor.className = "aviso " + (malo ? "aviso-error" : "aviso-ok");
    contenedor.innerHTML = (malo && resultado.regla
        ? '<span class="aviso-regla">' + resultado.regla + "</span> " : "") +
      escapar(resultado.mensaje);
    contenedor.hidden = false;
  }

  function limpiarAviso(contenedor) { if (contenedor) { contenedor.hidden = true; } }

  // Carrito de la sesion (vive fuera de la base: es estado del cliente).
  var Carrito = {
    CLAVE: "vetpet.connect.carrito.v1",
    leer: function () {
      try { return JSON.parse(sessionStorage.getItem(Carrito.CLAVE) || "[]"); }
      catch (e) { return []; }
    },
    escribir: function (c) {
      try { sessionStorage.setItem(Carrito.CLAVE, JSON.stringify(c)); } catch (e) {}
    },
    vaciar: function () { Carrito.escribir([]); },
    unidades: function () {
      return Carrito.leer().reduce(function (t, l) { return t + l.cantidad; }, 0);
    }
  };

  global.App = {
    Sesion: Sesion, Carrito: Carrito, MODULOS: MODULOS,
    pintarCabecera: pintarCabecera, escapar: escapar, soles: soles,
    avisar: avisar, limpiarAviso: limpiarAviso
  };
})(window);
