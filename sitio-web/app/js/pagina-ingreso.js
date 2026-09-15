/* Ingreso a la plataforma: RN-01 (rol excluyente), RN-02 (correo único),
   RN-03 (la contraseña nunca se guarda legible). */
(function () {
  "use strict";
  var d = Datos.cargar();
  var avisoIngreso  = document.getElementById("aviso-ingreso");
  var avisoRegistro = document.getElementById("aviso-registro");

  // Cuentas de demostración: un clic entra con ese rol.
  var CLAVE_DEMO = "demo123";
  document.getElementById("lista-cuentas").innerHTML = d.usuarios.slice(0, 5).map(function (u) {
    return '<button type="button" class="cuenta-demo" data-correo="' + u.correo + '">' +
             '<span><strong>' + App.escapar(u.nombre) + '</strong>' +
             '<small>' + u.correo + ' · ' + CLAVE_DEMO + '</small></span>' +
             '<span class="pastilla-rol">' + u.rol + '</span>' +
           '</button>';
  }).join("");

  document.getElementById("lista-cuentas").addEventListener("click", function (e) {
    var b = e.target.closest("[data-correo]");
    if (b) { entrar(b.getAttribute("data-correo")); }
  });

  document.getElementById("form-ingreso").addEventListener("submit", function (e) {
    e.preventDefault();
    entrar(document.getElementById("correo").value.trim());
  });

  function entrar(correo) {
    var u = d.usuarios.find(function (x) {
      return x.correo.toLowerCase() === String(correo).toLowerCase();
    });
    if (!u) {
      App.avisar(avisoIngreso, { ok: false, mensaje: "No existe una cuenta con ese correo." });
      return;
    }
    var rol = Reglas.rn01ValidarRol(u.rol);          // RN-01
    if (!rol.ok) { App.avisar(avisoIngreso, rol); return; }

    App.Sesion.abrir({ usuario_id: u.usuario_id, nombre: u.nombre, correo: u.correo, rol: u.rol });
    location.href = (App.MODULOS[u.rol] || [["index.html"]])[0][0];
  }

  document.getElementById("form-registro").addEventListener("submit", function (e) {
    e.preventDefault();
    var nombre = document.getElementById("reg-nombre").value.trim();
    var correo = document.getElementById("reg-correo").value.trim();
    var clave  = document.getElementById("reg-clave").value;

    var unico = Reglas.rn02CorreoUnico(d.usuarios, correo);   // RN-02
    if (!unico.ok) { App.avisar(avisoRegistro, unico); return; }

    Reglas.rn03Hash(clave).then(function (hash) {             // RN-03
      d.usuarios.push({
        usuario_id: Datos.siguiente("usuario"),
        nombre: nombre, correo: correo, password_hash: hash,
        rol: "CLIENTE", telefono: ""
      });
      Datos.guardar();
      App.avisar(avisoRegistro, { ok: true, mensaje:
        "Cuenta creada con rol CLIENTE. Se guardó el hash " + hash.slice(0, 16) +
        "…, no la contraseña." });
      e.target.reset();
    });
  });
})();
