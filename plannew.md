Nueva funcionalidad: Facilidades de intercambio

Se creará una nueva funcionalidad para gestionar facilidades de intercambio entre usuarios.

Esta opción incluirá lo siguiente:

Un listado de productos que el usuario desea intercambiar.
Los productos podrán clasificarse como:
Solo intercambio
Intercambio + venta
El usuario podrá seleccionar uno o varios productos mediante checkbox.

Flujo del proceso:

Al seleccionar los productos y enviar la solicitud, se generará un mensaje al receptor indicando que tiene una propuesta de intercambio.
En la vista de recepción de intercambios, el receptor podrá:
Aceptar
Rechazar
Si rechaza:
Se enviará una notificación al emisor indicando el rechazo.
Si acepta:
Se enviará una notificación al emisor indicando la aceptación.
El producto se marcará en color verde como “en proceso de intercambio”.
Luego de la aceptación, el usuario emisor (quien inició el intercambio) deberá aprobar nuevamente el intercambio.
Una vez aprobado por el emisor:
Se habilitará la opción de pago en la vista de recepción de intercambio para ambos usuarios.
Cuando ambos procesos estén completados:
Se notificará a los administradores que el proceso fue completado, para proceder con el envío de los productos intercambiados.



🧩 Historias de Usuario
1. Crear propuesta de intercambio

Como usuario
Quiero seleccionar uno o varios productos
Para enviarlos como propuesta de intercambio

Criterios:

Puede seleccionar múltiples productos (checkbox)
Puede marcar tipo:
Intercambio
Intercambio + venta
Se envía notificación al receptor
2. Recibir propuesta

Como receptor
Quiero ver la propuesta
Para aceptarla o rechazarla

Criterios:

Puede aceptar o rechazar
Si rechaza → notificación al emisor
Si acepta → productos cambian a estado “En intercambio”
3. Confirmación del emisor

Como emisor
Quiero confirmar la aceptación
Para continuar el proceso

Criterios:

Debe aprobar después del receptor
Sin esta aprobación no hay pago
4. Pago

Como usuario (ambos)
Quiero realizar el pago (si aplica)
Para completar el intercambio

Criterios:

Solo se habilita cuando ambos aceptan
Aplica si es “intercambio + venta”
5. Notificación a administradores

Como sistema
Quiero notificar a admin
Para gestionar el envío