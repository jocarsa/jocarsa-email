# jocarsa-email

# Diagrama de comportamiento

```mermaid
    graph TD;
    RecogerDatos[Recogemos datos de GET y POST] --> AgregarCampos[Agregamos campos de fecha, url de referencia, user agent];
    AgregarCampos --> EnviarEmail[Enviamos el correo];
```
