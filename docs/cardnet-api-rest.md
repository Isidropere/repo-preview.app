Integración sin Pantalla (REST)
Preámbulo
Esta guía describe los métodos y parámetros disponibles en la plataforma Ztrans utilizada para procesar transacciones de comercio electrónico con el protocolo de comunicación web services para comercios afiliados a Cardnet. Está dirigida a desarrolladores y equipos técnicos responsables de implementar y mantener la integración con Ztrans, asegurando que comprendan los métodos y parámetros necesarios para procesar transacciones de manera segura y eficiente.

Descripción general de la plataforma Ztrans
Ztrans es una plataforma avanzada de comercio electrónico que permite a los comerciantes procesar transacciones de pago de manera segura y eficiente. A través de la integración con los servicios web de Ztrans, los comerciantes pueden aceptar pagos con tarjetas de crédito y débito, gestionar transacciones autenticadas con 3D Secure, realizar devoluciones, y más. La plataforma utiliza tecnología de punta para asegurar la confidencialidad, integridad y disponibilidad de las transacciones, cumpliendo con los estándares de seguridad más exigentes.

Consideraciones
Se debe tener en cuenta que la plataforma de comercio electrónico utiliza un certificado digital con encriptación TLS1.2, por lo tanto, las aplicaciones deberán soportar este nivel de encriptación.

El proceso de cierre de los comercios es ejecutado de forma automática en la plataforma a las 7:00 PM. En caso de necesitar hacer el cierre a una hora diferente, deberá especificarlo a su ejecutivo de cuenta.

Los parámetros establecidos en este documento son definidos únicamente para el ambiente de certificaciones (QA), Los datos de producción deberán solicitarse al ejecutivo de cuenta.

Requisitos previos
Antes de comenzar con la integración, asegúrate de contar con las siguientes herramientas y conocimientos:

Cuenta en Ztrans: Datos de pruebas como número de afiliado y terminal ID para realizar transacciones. Revisa el capítulo 5 para más detalles.
Conocimientos técnicos: Familiaridad con los conceptos de programación web, servicios web REST y manejo de APIs.
Entorno de desarrollo: Un entorno de desarrollo integrado (IDE) como Visual Studio Code, IntelliJ IDEA, o cualquier otro de tu preferencia.
Certificado TLS 1.2: Asegúrate de que tu aplicación soporte encriptación TLS 1.2 para la comunicación segura con los servicios de Ztrans.
URL Base
https://ecommerce.cardnet.com.do/api/payment

Métodos Disponibles
Ztrans ofrece varios métodos a través de sus servicios web para gestionar diferentes aspectos de las transacciones. A continuación, se describen los métodos soportados.

Create Idempotency Key: Método utilizado para obtener el idempotency-key. Este valor debe solicitarse antes de cada requerimiento de pago. Primero se debe obtener el idempotency-key y luego agregarlo al mensaje de solicitud de pago (Sale).

Process Sale: Método utilizado para enviar requerimientos de venta con tarjeta. Cada solicitud debe incluir el idempotency-key previamente generado. Una vez completada la transacción, debería recibirse el código de respuesta 00 y la descripción “Transaction Approved”.

Process Void: Método utilizado para anular un pago previamente autorizado. Debe enviarse la información obtenida de la respuesta del pago. Las anulaciones solo pueden enviarse antes de realizar el proceso de cierre.

Process CheckIn: Método utilizado para realizar un check-in y/o cargo autorizado al disponible de un TH. Para liquidar esta transacción, debe enviarse una confirmación o check-out con los datos de la transacción original.

Process CheckOut: Método utilizado para realizar un check-out de un cargo autorizado anteriormente al disponible de un TH. Con esta confirmación, se podrá liquidar la transacción original. Este proceso puede aplicarse con un monto igual o menor a la transacción original.

Refund: Método utilizado para realizar una devolución de una transacción previamente autorizada. El monto a devolver puede ser parcial o total, pero no mayor que la transacción original.

Lista de Parámetros
token (string) [Mandatorio, Longitud: Variable] - Valor generado por la aplicación del cliente.

idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción.

merchant-id (string) [Mandatorio, Longitud: 15] - Número de afiliado del comercio.

terminal-id (string) [Mandatorio, Longitud: 8] - Número de terminal id del comercio.

card-number (string) [Mandatorio, Longitud: Variable] - Número de tarjeta utilizada para realizar el pago.

environment (string) [Mandatorio, Longitud: Variable] - Valor utilizado para identificar el tipo de transacción:

ASI: Registro de tarjeta con monto cero.
Ecommerce: Transacción de comercio electrónico.
Ecommerce_COF: Transacción Ecommerce con credencial archivada.
MOTO: Transacción de pedido por correo.
MOTO_Recurring: Transacción de pago recurrente con credencial archivada.
QuasiCashMT: Transacción para juegos de azar.
Nota: Algunos casos de uso son definidos en la configuración del afiliado una vez creado en el sistema de CardNET, se debe informar el caso de uso al momento de solicitar los datos de afiliación.

pstr43 (string) [Opcional, Longitud: Máx. 25] - Parámetro opcional para autenticar la transacción.

pstr63 (string) [Opcional, Longitud: Máx. 3] - Parámetro opcional para las transacciones en cuotas y puntos (C06, P00).

expiration-date (string) [Mandatorio, Longitud: 5] - Fecha de expiración de la tarjeta en formato MM/YY.

cvv (string) [Condicional, Longitud: 3-4] - Número de validación de tarjeta.

amount (decimal) [Mandatorio, Longitud: Variable] - Monto de la transacción.

tip (decimal) [Opcional, Longitud: Variable] - Monto de propinas.

tax (decimal) [Opcional, Longitud: Variable] - Monto de impuesto pagado.

currency (string) [Mandatorio, Longitud: 3] - Código de moneda.

invoice-number (string) [Mandatorio, Longitud: Máx. 15] - Número de referencia del sistema. Este campo permite recibir un valor de número de referencia generado por el sistema cliente para realizar la trazabilidad de una transacción.

client-ip (string) [Mandatorio, Longitud: Variable] - Dirección IP de la aplicación cliente.

reference-number (string) [Mandatorio, Longitud: Variable] - Número de referencia del pago.

tds_mode (string) [Condicional, Longitud: 1] - Este campo indica el modo de procesamiento de las transacciones autenticadas con el servicio 3D Secure:

1: Indica que los datos de autenticación son obtenidos a través del servidor de autenticación por medio del parámetro 3dstransactionid.
2: Indica que los datos de autenticación son recibidos por la aplicación cliente.
tds_servertransactionid (string) [Condicional, Longitud: Variable] - Identificador de transacción generado por el servidor de autenticación 3D Secure.

tds_eci (string) [Condicional, Longitud: Variable] - Indicador de comercio electrónico para 3D Secure.

tds_aav (string) [Condicional, Longitud: Variable] - Valor de verificación de autenticación 3D Secure.

tds_programprotocol (string) [Condicional, Longitud: Variable] - Tipo de protocolo utilizado para la autenticación 3D Secure.

tds_dstransactionid (string) [Condicional, Longitud: Variable] - Identificador de autenticación generado por el directorio de la marca.

tds_status (string) [Condicional, Longitud: Variable] - Código de estado para la autenticación, necesario para 3DS-Mode=2.

response-code (string) [Condicional, Longitud: Variable] - Código de respuesta del autorizador de tarjeta.

internal-response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta interno de la plataforma.

response-code-desc (string) [Mandatorio, Longitud: Variable] - Descripción del código de respuesta.

response-code-source (string) [Mandatorio, Longitud: Variable] - Origen de la respuesta:

gw para respuesta de la plataforma.
icache para respuesta generada de una transacción con el mismo idempotency-key.
approval-code (string) [Condicional, Longitud: Variable] - Número de autorización del banco emisor.

pnRef (string) [Condicional, Longitud: Variable] - Número de referencia de la plataforma.

tkn_eci (string) [Condicional, Longitud: Variable] - Indicador de comercio electrónico para transacciones con token de red. Valores:

Visa:
05: Secure electronic commerce transaction.
07: Non-authenticated security transaction.
MasterCard:
06: Regular COF Transaction, first authorization for recurring payments.
07: Subsecuencia de una transacción de recurrencia.
Nota: Para una transacción con autenticación 3D Secure, el campo ECI de la autenticación tendrá preferencia.

tkn_tavv (string) [Condicional, Longitud: Variable] - Valor de verificación o criptograma generado para realizar el pago.

tkn_reqid (string) [Condicional, Longitud: Variable] - Valor identificador de la entidad que solicitó el token generado por la marca para el comercio.

tkn_assurance (string) [Condicional, Longitud: Variable] - Información sobre el nivel de garantía y la autenticidad del token utilizado en la transacción.

Notas:
Algunos parámetros como cvv y token son opcionales o condicionales dependiendo del tipo de transacción.
Para transacciones con autenticación 3DS y token, se deben enviar los parámetros específicos según el caso.
CreateIdempotencyKey
Método utilizado para obtener el idempotencyKey, este valor deberá solicitarse antes de cada requerimiento de pago, o sea, primero se debe obtener el idempotencyKey y luego agregarlo al mensaje de solicitud de pago (processsale).

Ruta: /api/payment/idempotency-keys
Método: POST
Ejemplo de solicitud método CreateIdempotencyKey

curl -X POST https://ecommerce.cardnet.com.do/api/payment/idempotency-keys
     -H "Accept: text/plain"
Ejemplo de respuesta método CreateIdempotencyKey

ikey:c0930d79506f4cc49730232b0f6e0b5c
Sale
Método utilizado para enviar requerimientos de venta con tarjeta, se debe tener en cuenta que en cada solicitud deberá enviarse el valor de idempotencyKey previamente generado. Una vez que la transacción sea completada debería recibir el código de respuesta 00 y la descripción “Transaction Approved”.

Ruta: /api/payment/transactions/sales
Método: POST
Parámetros del método ProcessSale
token (string) [Opcional, Longitud: Variable] - Valor generado por la aplicación del cliente.

idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción.

merchant-id (string) [Mandatorio, Longitud: 15] - Número de afiliado del comercio.

terminal-id (string) [Mandatorio, Longitud: 8] - Número de terminal ID del comercio.

card-number (string) [Mandatorio, Longitud: Variable] - Número de tarjeta utilizada para realizar el pago.

environment (string) [Mandatorio, Longitud: Variable] - Valor utilizado para identificar el tipo de transacción:

ASI: Registro de tarjeta con monto cero.
Ecommerce: Transacción de comercio electrónico.
Ecommerce_COF: Transacción Ecommerce con credencial archivada.
MOTO: Transacción de pedido por correo.
MOTO_Recurring: Transacción de pago recurrente con credencial archivada.
QuasiCashMT: Transacción para juegos de azar.
Nota: Algunos casos de uso son definidos en la configuración del afiliado una vez creado en el sistema de CardNET, se debe informar el caso de uso al momento de solicitar los datos de afiliación.

pstr43 (string) [Opcional, Longitud: Máx. 25] - Parámetro opcional para autenticar la transacción.

pstr63 (string) [Opcional, Longitud: Máx. 3] - Parámetro opcional para las transacciones en cuotas y puntos (C06, P00).

expiration-date (string) [Mandatorio, Longitud: 5] - Fecha de expiración de la tarjeta en formato MM/YY.

cvv (string) [Condicional, Longitud: 3-4] - Número de validación de tarjeta.

amount (decimal) [Mandatorio, Longitud: Variable] - Monto de la transacción.

tip (decimal) [Opcional, Longitud: Variable] - Monto de propinas.

tax (decimal) [Opcional, Longitud: Variable] - Monto de impuesto pagado.

currency (string) [Mandatorio, Longitud: 3] - Código de moneda.

invoice-number (string) [Mandatorio, Longitud: Máx. 15] - Número de referencia del sistema. Este campo permite recibir un valor de número de referencia generado por el sistema cliente para realizar la trazabilidad de una transacción.

client-ip (string) [Mandatorio, Longitud: Variable] - Dirección IP de la aplicación cliente.

reference-number (string) [Mandatorio, Longitud: Variable] - Número de referencia del pago.

operation (string) [Opcional, Longitud: Variable] - Parámetro utilizado para especificar el tipo de operación. Actualmente, el único valor válido es refund.

tds_mode (string) [Condicional, Longitud: 1] - Este campo indica el modo de procesamiento de las transacciones autenticadas con el servicio 3D Secure:

1: Indica que los datos de autenticación son obtenidos a través del servidor de autenticación por medio del parámetro 3dstransactionid.
2: Indica que los datos de autenticación son recibidos por la aplicación cliente.
tds_servertransactionid (string) [Condicional, Longitud: Variable] - Identificador de transacción generado por el servidor de autenticación 3D Secure.

tds_eci (string) [Condicional, Longitud: Variable] - Indicador de comercio electrónico para 3D Secure.

tds_aav (string) [Condicional, Longitud: Variable] - Valor de verificación de autenticación 3D Secure.

tds_programprotocol (string) [Condicional, Longitud: Variable] - Tipo de protocolo utilizado para la autenticación 3D Secure.

tds_dstransactionid (string) [Condicional, Longitud: Variable] - Identificador de autenticación generado por el directorio de la marca.

tds_status (string) [Condicional, Longitud: Variable] - Código de estado para la autenticación, necesario para 3DS-Mode=2.

tkn_eci (string) [Condicional, Longitud: Variable] - Indicador de comercio electrónico para transacciones con token de red. Valores:

Visa:
05: Secure electronic commerce transaction.
07: Non-authenticated security transaction.
MasterCard:
06: Regular COF Transaction, first authorization for recurring payments.
07: Subsecuencia de una transacción de recurrencia.
Nota: Para una transacción con autenticación 3D Secure, el campo ECI de la autenticación tendrá preferencia.

tkn_tavv (string) [Condicional, Longitud: Variable] - Valor de verificación o criptograma generado para realizar el pago.

tkn_reqid (string) [Condicional, Longitud: Variable] - Valor identificador de la entidad que solicitó el token generado por la marca para el comercio.

tkn_assurance (string) [Condicional, Longitud: Variable] - Información sobre el nivel de garantía y la autenticidad del token utilizado en la transacción.

Ejemplo de solicitud del método ProcessSale

curl -X POST https://ecommerce.cardnet.com.do/api/payment/transactions/sales
     -H "Content-Type: application/json"
     -H "Accept: application/json"
     -d '{
        "amount": 1234.87,
        "card-number": "41111******1111",
        "client-ip": "10.100.4.64",
        "currency": "214",
        "cvv": "468",
        "environment": "ECommerce",
        "expiration-date": "06/25",
        "idempotency-key": "098900879879645694198a1902411fa9",
        "invoice-number": "000011",
        "merchant-id": "349011300",
        "reference-number": "0000011",
        "tax": 0,
        "terminal-id": "00567856",
        "tip": 0,
        "token": "454500350001"
     }'
Parámetros de respuesta del método ProcessSale
idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción.
response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta del autorizador de tarjeta.
internal-response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta interno de la plataforma.
response-code-desc (string) [Mandatorio, Longitud: Variable] - Descripción del código de respuesta.
response-code-source (string) [Mandatorio, Longitud: Variable] - Origen de la respuesta:
gw: Respuesta de la plataforma.
icache: Respuesta generada de una transacción con el mismo idempotency-key.
approval-code (string) [Condicional, Longitud: Variable] - Número de autorización del banco emisor.
pnRef (string) [Mandatorio, Longitud: Variable] - Número de referencia de la plataforma.
Ejemplo de respuesta del método ProcessSale

{
  "idempotency-key": "098900879879645694198a1902411fa9",
  "response-code": "00",
  "internal-response-code": "0000",
  "response-code-desc": "Transaction Approved",
  "response-code-source": "gw",
  "approval-code": "008766",
  "pnRef": "txn-2j4S6en5X1JGnV956UtCiW76bUg"
}
Void
Método utilizado para anular un pago previamente autorizado. Para consumir este método deberá enviar información obtenida de la respuesta del pago. Las anulaciones solamente podrían enviarse antes de realizar el proceso de cierre.

Ruta: /api/payment/transactions/voids
Método: POST
Parámetros para solicitud del método Void
token (string) [Mandatorio, Longitud: Variable] - Valor generado por la aplicación del cliente.

idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción original.

merchant-id (string) [Mandatorio, Longitud: 15] - Número de afiliado del comercio.

terminal-id (string) [Mandatorio, Longitud: 8] - Número de terminal ID del comercio.

environment (string) [Condicional, Longitud: Variable] - Valor utilizado para identificar el tipo de transacción:

ASI: Registro de tarjeta con monto cero.
Ecommerce: Transacción de comercio electrónico.
Ecommerce_COF: Transacción Ecommerce con credencial archivada.
MOTO: Transacción de pedido por correo.
MOTO_Recurring: Transacción de pago recurrente con credencial archivada.
QuasiCashMT: Transacción para juegos de azar.
Nota: Algunos casos de uso son definidos en la configuración del afiliado una vez creado en el sistema de CardNET, se debe informar el caso de uso al momento de solicitar los datos de afiliación.

amount (decimal) [Mandatorio, Longitud: Variable] - Monto de la transacción a anular.

currency (string) [Mandatorio, Longitud: 3] - Código de moneda.

pnRef (string) [Mandatorio, Longitud: Variable] - Número de referencia de la transacción original a anular.

Ejemplo de solicitud del método Void

curl -X POST https://ecommerce.cardnet.com.do/api/payment/transactions/voids
     -H "Content-Type: application/json"
     -H "Accept: application/json"
     -d '{
        "amount": 1234.87,
        "client-ip": "10.100.4.64",
        "currency": "214",
        "environment": "ECommerce",
        "idempotency-key": "098900879879645694198a1902411fa9",
        "pnRef": " txn-2j4S6en5X1JGnV956UtCiW76bUg ",
        "invoice-number": "000011",
        "merchant-id": "349011300",
        "reference-number": "0000011",
        "terminal-id": "00567856",
        "token": "454500350001"
     }'
Parámetros de respuesta del método Void
idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción.
response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta del autorizador de tarjeta.
internal-response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta interno de la plataforma.
response-code-desc (string) [Mandatorio, Longitud: Variable] - Descripción del código de respuesta.
response-code-source (string) [Mandatorio, Longitud: Variable] - Origen de la respuesta:
gw: Respuesta de la plataforma.
icache: Respuesta generada de una transacción con el mismo idempotency-key.
approval-code (string) [Condicional, Longitud: Variable] - Número de autorización generado por el banco emisor.
pnRef (string) [Mandatorio, Longitud: Variable] - Número de referencia de la plataforma.
Ejemplo de respuesta del método Void

{
  "idempotency-key": "098900879879645694198a1902411fa9",
  "response-code": "00",
  "internal-response-code": "0000",
  "response-code-desc": "Transaction Approved",
  "response-code-source": "gw",
  "approval-code": "008766",
  "pnRef": "txn-2j4S6en5X1JGnV956UtCiW76bUg"
}
CheckIn
Método utilizado para realizar un check in y/o cargo autorizado al disponible de un TH. Para poder liquidar esta transacción deberá enviar una confirmación o check out con los datos de la transacción original.

Ruta: /api/payment/transactions/checkins
Método: POST
Parámetros para solicitud del método CheckIn
token (string) [Opcional, Longitud: Variable] - Valor generado por la aplicación del cliente.

idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción.

merchant-id (string) [Mandatorio, Longitud: 15] - Número de afiliado del comercio.

terminal-id (string) [Mandatorio, Longitud: 8] - Número de terminal ID del comercio.

card-number (string) [Mandatorio, Longitud: Variable] - Número de tarjeta utilizada para realizar el pago.

environment (string) [Mandatorio, Longitud: Variable] - Valor utilizado para identificar el tipo de transacción:

ASI: Registro de tarjeta con monto cero.
Ecommerce: Transacción de comercio electrónico.
Ecommerce_COF: Transacción Ecommerce con credencial archivada.
MOTO: Transacción de pedido por correo.
MOTO_Recurring: Transacción de pago recurrente con credencial archivada.
QuasiCashMT: Transacción para juegos de azar.
Nota: Algunos casos de uso son definidos en la configuración del afiliado una vez creado en el sistema de CardNET, se debe informar el caso de uso al momento de solicitar los datos de afiliación.

pstr43 (string) [Opcional, Longitud: Máx. 25] - Parámetro opcional para autenticar la transacción.

expiration-date (string) [Mandatorio, Longitud: 5] - Fecha de expiración de la tarjeta en formato MM/YY.

cvv (string) [Condicional, Longitud: 3-4] - Número de validación de tarjeta.

amount (decimal) [Condicional, Longitud: Variable] - Monto de la transacción.

tip (decimal) [Mandatorio, Longitud: Variable] - Monto de propinas.

tax (decimal) [Opcional, Longitud: Variable] - Monto de impuesto pagado.

currency (string) [Opcional, Longitud: 3] - Código de moneda.

invoice-number (string) [Mandatorio, Longitud: Máx. 15] - Número de referencia del sistema. Este campo permite recibir un valor de número de referencia generado por el sistema cliente para realizar la trazabilidad de una transacción.

client-ip (string) [Mandatorio, Longitud: Variable] - Dirección IP de la aplicación cliente.

reference-number (string) [Mandatorio, Longitud: Variable] - Número de referencia del pago.

tds_mode (string) [Condicional, Longitud: 1] - Este campo indica el modo de procesamiento de las transacciones autenticadas con el servicio 3D Secure:

1: Indica que los datos de autenticación son obtenidos a través del servidor de autenticación por medio del parámetro 3dstransactionid.
2: Indica que los datos de autenticación son recibidos por la aplicación cliente.
tds_servertransactionid (string) [Condicional, Longitud: Variable] - Identificador de transacción generado por el servidor de autenticación 3D Secure.

tds_eci (string) [Condicional, Longitud: Variable] - Indicador de comercio electrónico para 3D Secure.

tds_aav (string) [Condicional, Longitud: Variable] - Valor de verificación de autenticación 3D Secure.

tds_programprotocol (string) [Condicional, Longitud: Variable] - Tipo de protocolo utilizado para la autenticación 3D Secure.

tds_dstransactionid (string) [Condicional, Longitud: Variable] - Identificador de autenticación generado por el directorio de la marca.

tds_status (string) [Condicional, Longitud: Variable] - Código de estado para la autenticación, necesario para 3DS-Mode=2.

tkn_eci (string) [Condicional, Longitud: Variable] - Indicador de comercio electrónico para transacciones con token de red. Valores:

Visa:
05: Secure electronic commerce transaction.
07: Non-authenticated security transaction.
MasterCard:
06: Regular COF Transaction, first authorization for recurring payments.
07: Subsecuencia de una transacción de recurrencia.
Nota: Para una transacción con autenticación 3D Secure, el campo ECI de la autenticación tendrá preferencia.

tkn_tavv (string) [Condicional, Longitud: Variable] - Valor de verificación o criptograma generado para realizar el pago.

tkn_reqid (string) [Condicional, Longitud: Variable] - Valor identificador de la entidad que solicitó el token generado por la marca para el comercio.

tkn_assurance (string) [Condicional, Longitud: Variable] - Información sobre el nivel de garantía y la autenticidad del token utilizado en la transacción.

Ejemplo de solicitud

curl -X POST https://ecommerce.cardnet.com.do/api/payment/transactions/checkins
     -H "Content-Type: application/json"
     -H "Accept: application/json"
     -d '{
        "amount": 100,
        "card-number": "4111********1111",
        "client-ip": "10.100.0.1",
        "currency": "214",
        "cvv": "147",
        "environment": "ECommerce",
        "expiration-date": "01/22",
        "idempotency-key": "a4578bc997454c6bb4998f068b1af041",
        "invoice-number": "000001",
        "merchant-id": "349011300",
        "reference-number": "000001",
        "tax": 0.00,
        "terminal-id": "00567937",
        "tip": 0.00,
        "token": "454500350001"
     }'
Parámetros de Respuesta del método CheckIn
idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción.
response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta del autorizador de tarjeta.
internal-response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta interno de la plataforma.
response-code-desc (string) [Mandatorio, Longitud: Variable] - Descripción del código de respuesta.
response-code-source (string) [Mandatorio, Longitud: Variable] - Origen de la respuesta:
gw: Respuesta de la plataforma.
icache: Respuesta generada de una transacción con el mismo idempotency-key.
approval-code (string) [Condicional, Longitud: Variable] - Número de autorización del banco emisor.
trToken (string) [Mandatorio, Longitud: Variable] - Token de la respuesta de la transacción original realizada, utilizado para la transacción de Check-out.
Ejemplo de respuesta del método CheckIn

{
  "approval-code": "00551Z",
  "idempotency-key": "a4578bc997454c6bb4998f068b1af041",
  "internal-response-code": "0000",
  "response-code": "00",
  "response-code-desc": "Transaction Approved",
  "response-code-source": "gw",
  "txToken": "txn-0dceed0d6bb64633bbd5f24702193f5a"
}
CheckOut
Método utilizado para realizar un Check out de un cargo autorizado con anterioridad al disponible de un TH. Con esta confirmación se podrá liquidar la transacción original. Este podrá ser aplicado con un monto igual o menor a la transacción original.

Ruta: /api/payment/transactions/checkouts
Método: POST
Parámetros para solicitud
Parámetros para solicitud del método CheckOut
token (string) [Mandatorio, Longitud: Variable] - Valor generado por la aplicación del cliente.

idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción, debe agregarse el valor del requerimiento original.

merchant-id (string) [Mandatorio, Longitud: 15] - Número de afiliado del comercio.

terminal-id (string) [Condicional, Longitud: 8] - Número de terminal ID del comercio.

environment (string) [Mandatorio, Longitud: Variable] - Valor utilizado para identificar el tipo de transacción:

ASI: Registro de tarjeta con monto cero.
Ecommerce: Transacción de comercio electrónico.
Ecommerce_COF: Transacción Ecommerce con credencial archivada.
MOTO: Transacción de pedido por correo.
MOTO_Recurring: Transacción de pago recurrente con credencial archivada.
QuasiCashMT: Transacción para juegos de azar.
Nota: Algunos casos de uso son definidos en la configuración del afiliado una vez creado en el sistema de CardNET, se debe informar el caso de uso al momento de solicitar los datos de afiliación.

amount (decimal) [Mandatorio, Longitud: Variable] - Monto de la transacción a confirmar, ya sea igual o menor a la transacción original.

itbis (decimal) [Mandatorio, Longitud: Variable] - Monto del ITBIS del monto a confirmar.

client-ip (string) [Mandatorio, Longitud: Variable] - Dirección IP de la aplicación cliente.

reference-number (string) [Mandatorio, Longitud: Variable] - Número de referencia de la transacción definida por el cliente.

approval-code (string) [Condicional, Longitud: Variable] - Número de autorización del Check-in original de la transacción.

txToken (string) [Mandatorio, Longitud: Variable] - Token de la respuesta de la transacción original realizada.

Ejemplo de solicitud del método CheckOut

curl -X POST https://ecommerce.cardnet.com.do/api/payment/transactions/checkouts
     -H "Content-Type: application/json"
     -H "Accept: application/json"
     -d '{
       "idempotency-key":"eda518c9559c4c588f7c1cbd41a05917",
       "merchant-id":"349000000",
       "terminal-id":"58585858",
       "environment":"ECommerce",
       "amount": 350.00,
       "itbis": 50.00,
       "client-ip":"191.160.10.2",
       "reference-number":"12345",
       "approval-code": "030303",
       "txToken": "txn-7839e7cee09f47668a77b72554f44b02"
     }'
Parámetros de Respuesta del método CheckOut
idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción.
response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta del autorizador de tarjeta.
internal-response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta interno de la plataforma.
response-code-desc (string) [Mandatorio, Longitud: Variable] - Descripción del código de respuesta.
response-code-source (string) [Mandatorio, Longitud: Variable] - Origen de la respuesta:
gw: Respuesta de la plataforma.
icache: Respuesta generada de una transacción con el mismo idempotency-key.
approval-code (string) [Condicional, Longitud: Variable] - Número de autorización generado por el banco emisor referente a la transacción original.
txToken (string) [Mandatorio, Longitud: Variable] - Número de referencia de la plataforma.
Ejemplo de respuesta del método CheckOut

{
  "approval-code": "00551Z",
  "idempotency-key": "a4578bc997454c6bb4998f068b1af041",
  "internal-response-code": "0000",
  "response-code": "00",
  "response-code-desc": "Transaction Approved",
  "response-code-source": "gw",
  "txToken": "txn-0dceed0d6bb64633bbd5f24702193f5a"
}
Refund
Método utilizado para realizar una devolución de una transacción previamente autorizada, el monto a devolver puede ser parcial o total pero no mayor que la transacción original. Además, debido a que es una devolución vinculada a una transacción original, esta operación solamente se podrá realizar una única vez.

Por otro lado, como medida de seguridad, para consumir el método refund, necesitará obtener un bearer token, el cual permitirá consumir el servicio por un periodo de 1 hora. Las credenciales del token deberán ser solicitadas a través de su ejecutivo de cuentas. Una vez que el token es vencido, podrá volver a solicitarlo y someter las transacciones durante su tiempo de vigencia.

Ruta: /api/payment/transactions/refund
Método: POST
Parámetros para solicitud del método Refund
operation (string) [Mandatorio, Longitud: Variable] - Parámetro utilizado para especificar el tipo de operación. Actualmente, el único valor válido es refund.
idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción, debe agregarse el valor del requerimiento original.
merchant-id (string) [Mandatorio, Longitud: 15] - Número de afiliado del comercio.
terminal-id (string) [Mandatorio, Longitud: 8] - Número de terminal ID del comercio.
amount (decimal) [Mandatorio, Longitud: Variable] - Monto que desea devolver, este puede ser igual o menor a la transacción original.
currency (string) [Mandatorio, Longitud: 3] - Código de moneda.
invoice-number (string) [Mandatorio, Longitud: Máx. 15] - Número de referencia del sistema.
reference-number (string) [Mandatorio, Longitud: Variable] - Referencia de la transacción definida por el cliente.
pstr43 (string) [Opcional, Longitud: Variable] - Parámetro que permite especificar si luego de autorizar la devolución se desea hacer el cierre de la transacción de inmediato. Si no se desea, no debe enviarse en el requerimiento.
client-ip (string) [Mandatorio, Longitud: Variable] - Dirección IP de la aplicación cliente.
txToken (string) [Mandatorio, Longitud: Variable] - Token de la respuesta de la transacción original realizada.
token (string) [Mandatorio, Longitud: Variable] - Valor generado por la aplicación del cliente.
Ejemplo de solicitud del método Refund

curl -X POST https://ecommerce.cardnet.com.do/api/payment/transactions/refund
     -H "Content-Type: application/json"
     -H "Accept: application/json"
     -d '{
        "operation": "refund",
        "idempotency-key": "458900879879645694198a1902411fa9",
        "merchant-id": "349000000",
        "terminal-id": "12345678",
        "amount": 1000,
        "currency": "214",
        "invoice-number": "000011",
        "reference-number": "0000011",
        "client-ip": "10.100.4.64",
        "txToken": "txn-2j77PKzCLHLCLIQOOx1PhyRYrWt",
        "token": "454500350001"
     }'
Parámetros de Respuesta del método Refund
idempotency-key (string) [Mandatorio, Longitud: Variable] - Valor identificador de transacción.
response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta del autorizador de tarjeta.
internal-response-code (string) [Mandatorio, Longitud: Variable] - Código de respuesta interno de la plataforma.
response-code-desc (string) [Mandatorio, Longitud: Variable] - Descripción del código de respuesta.
response-code-source (string) [Mandatorio, Longitud: Variable] - Origen de la respuesta:
gw: Respuesta de la plataforma.
icache: Respuesta generada de una transacción con el mismo idempotency-key.
approval-code (string) [Condicional, Longitud: Variable] - Número de autorización generado por el banco emisor, referente a la transacción original.
txToken (string) [Mandatorio, Longitud: Variable] - Número de referencia de la plataforma.
Ejemplo de respuesta del método Refund

{
  "idempotency-key": "458900879879645694198a1902411fa9",
  "response-code": "00",
  "internal-response-code": "0000",
  "response-code-desc": "Transaction Approved",
  "response-code-source": "gw",
  "approval-code": "008766",
  "pnRef": "txn-2j4S6en5X1JGnV956UtCiW76bUg"
}
Flujo de transacción
Flujo REST

Parametros de prueba
Parámetros del Ambiente de QA
URL Base: https://labservicios.cardnet.com.do/api/payment
Numero de Comercio: 349041263
Terminal ID Comercio: 77777777
Currency: 214
Ambiente de producción
Parámetros del Ambiente de Producción
URL Base: https://ecommerce.cardnet.com.do/api/payment
Numero de Comercio: Solicitar al ejecutivo de cuenta.
Terminal ID Comercio: Solicitar al ejecutivo de cuenta.
Nota: Para autorizar en el ambiente de producción, debe solicitar los valores del número de comercio y terminal ID a su ejecutivo de cuenta.

Manejo de códigos de respuesta
Los códigos de respuestas generadas por los servicios Transacciónales se deben interpretar tomando en cuenta los campos internal-response-code y response-code.

Internal Response Code: Este código de 4 dígitos se refiere a la razón de una respuesta tomando en cuenta los datos entre la aplicación cliente y la plataforma. La descripción de estos códigos debería ser de uso internos para el comercio. No debería mostrarse al usuario final que realiza el pago en el comercio.

Response Code: Este código de 2 dígitos se refiere al código generado por el banco emisor. Además, la descripción de este

Lista de códigos de respuesta
Códigos de Aprobación y Errores Comunes
00 - Aprobada
01 - Llamar al Banco
02 - Llamar al Banco
05 - Rechazada
06 - Error en Mensaje
07 - Tarjeta Rechazada
08 - Llamar al Banco
09 - Request in progress
10 - Aprobación Parcial
11 - Approved VIP
12 - Transacción Inválida
13 - Monto Inválido
14 - Cuenta Inválida
21 - No tomo acción
Códigos de Fondos Insuficientes
51 - Fondos insuficientes
Códigos de Cuenta o Tarjeta Inválida
52 - Cuenta Invalida
53 - Cuenta Invalida
54 - Tarjeta vencida
Códigos de Rechazo por Seguridad
62 - Tarjeta Restringida
89 - Terminal Invalida
90 - Cierre en proceso
91 - Host No Disponible
Otros Códigos y Errores
96 - Error de Sistema
97 - Emisor no Disponible
99 - Error en CVV o CVC
4901 - Fallo de autenticación 3DS
