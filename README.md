# jocarsa-email

# Diagrama de comportamiento

```mermaid
    graph TD;
    RecogerDatos[Recogemos datos de GET y POST] --> AgregarCampos[Agregamos campos de fecha, url de referencia, user agent];
    AgregarCampos --> GenerarTabla[Generamos tabla];
    GenerarTabla --> ComprobarDominio[Comprobamos si el correo se envia desde jocarsa.com];
    ComprobarDominio -->AplicaSPAM[Aplicamos filtro de SPAM];
    AplicaSPAM --> ComprobamosMailValido[Comprobamos si es un email valido];
    ComprobamosMailValido --> GuardarJSON[Guardamos el correo como archivo json];
    GuardarJSON --> EnviarCorreo[Se envía solo si no es spam];
    EnviarCorreo --> MensajeExito[Se muestra mensaje de éxito];
    MensajeExito --> Redirigir[Se redirige en 5 segundos];
    
```
