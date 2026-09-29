# Generador del documento de requerimientos

Rehace `docs/requerimientos/Requerimientos_VetPetConnect.docx` a partir del texto
que vive en `datos.js`, para que el documento no se edite a mano y se pueda volver
a generar cuando la empresa cambie un requerimiento.

- `datos.js` — los 17 requerimientos funcionales, los 12 no funcionales y las 22
  reglas de negocio, redactados desde el pedido de la empresa.
- `generar.js` — la maquetación: portada, capítulos, fichas y tablas.

```bash
npm install docx
node herramientas/requerimientos/generar.js docs/requerimientos/Requerimientos_VetPetConnect.docx
```
