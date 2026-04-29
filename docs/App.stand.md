Información sobre PageSpeed Insights





Informes de PageSpeed Insights (PSI) sobre la experiencia del usuario de una página en dispositivos móviles y computadoras de escritorio y brinda sugerencias para mejorar esa página.

PSI proporciona datos de lab y de campo sobre una página. Los datos de lab son útiles para depurar ya que se recopilan en un entorno controlado. Sin embargo, es posible que no para capturar cuellos de botella del mundo real. Los datos de campo son útiles para captar datos de usuarios reales experiencia, pero tiene un conjunto de métricas más limitado. Consulta Cómo pensar las herramientas de velocidad para obtener más información sobre los dos tipos de datos.

Datos de la experiencia del usuario real
Los datos de la experiencia del usuario real en PSI se basan en el conjunto de datos del Informe sobre la experiencia del usuario en Chrome (CrUX). PSI informa datos de usuarios reales Primero Procesamiento de imagen con contenido (FCP), Interacción con el siguiente procesamiento (INP) Procesamiento de imagen con contenido más grande (LCP) y diseño acumulado Cambio (CLS) experimenta durante el período de recolección anterior de 28 días. PSI también informa experiencias para la métrica experimental Tiempo hasta el primer byte (TTFB).

Para mostrar los datos de la experiencia del usuario de una página determinada, debe haber suficientes datos. que se incluirán en el conjunto de datos de CrUX. Es posible que una página no tenga datos suficientes si se publicó recientemente o tiene muy pocos ejemplos de usuarios reales. Cuando esto sucede, PSI disminuye volver al nivel de detalle a nivel del origen, que abarca todas las experiencias del usuario en todas las páginas de la sitio web. A veces, es posible que el origen no tenga datos suficientes, en cuyo caso, PSI no era posible mostrar datos de la experiencia del usuario real.

Cómo evaluar la calidad de las experiencias
PSI clasifica la calidad de las experiencias de los usuarios en tres categorías: buena, necesita mejorar, o Deficiente. PSI establece los siguientes umbrales en alineación con la iniciativa de Métricas web esenciales:

Bueno	Requiere mejoras	Deficiente
FCP	[0, 1800 ms]	[1800 ms, 3000 ms]	Más de 3,000 ms
LCP	[0, 2,500 ms]	(2500ms, 4000ms]	Más de 4000 ms
CLS	[0, 0.1]	(0.1, 0.25]	superior a 0.25
INP	[0, 200ms]	[200 ms, 500 ms]	Más de 500 ms
TTFB (experimental)	[0, 800ms]	(800ms, 1800ms]	Más de 1800 ms
Distribución y valores de métricas seleccionadas
PSI presenta una distribución de estas métricas para que los desarrolladores puedan comprender el rango experiencias para esa página u origen. Esta distribución se divide en tres categorías: Buen rendimiento, Debe mejorar y Deficiente, que se representan con barras verdes, ámbar y rojas. Por ejemplo, ver un 11% dentro de la barra ámbar de LCP indica que el 11% de todos los valores de LCP observados deben estar entre 2500 ms y 4000 ms.

Captura de pantalla de la distribución de experiencias de LCP de usuarios reales
Por encima de las barras de distribución, PSI informa el percentil 75 de todas las métricas. Se selecciona el percentil 75 para que los desarrolladores puedan comprender las experiencias del usuario más frustrantes en su sitio. Estos valores de métricas de campo se clasifican como buenos, necesitan mejoras o son deficientes aplicando los mismos umbrales que se muestran anteriormente.

Métricas web esenciales
Las métricas web esenciales son un conjunto común de indicadores de rendimiento fundamentales para todas las experiencias web. Las métricas de las Métricas web esenciales son INP, LCP y CLS, y pueden agregados a nivel de la página o del origen. Para agregaciones con datos suficientes en todas tres métricas, la agregación pasa la evaluación de Core Web Vitals si el percentil 75 de las tres métricas son buenas. De lo contrario, la agregación no aprobará la evaluación. Si la agregación no tiene datos suficientes para la INP, se aprobará la evaluación si los percentiles 75 de la LCP y la CLS son buenos. Si el LCP o CLS no tienen datos suficientes, la página no se puede evaluar la agregación a nivel de origen.

Diferencias entre los datos de campo en PSI y CrUX
La diferencia entre los datos de campo en PSI y los El conjunto de datos de CrUX en BigQuery indica que los datos de PSI se actualizan a diario. mientras que el conjunto de datos de BigQuery se actualiza mensualmente y se limita a datos a nivel de origen. Ambas fuentes de datos representan períodos finales de 28 días.

Diagnósticos de lab
PSI usa Lighthouse para analizar la URL determinada en una simulación para las categorías Rendimiento, Accesibilidad, Prácticas recomendadas y SEO.

Puntuación
En la parte superior de la sección, se encuentran las puntuaciones de cada categoría, determinadas por la ejecución de Lighthouse para recopilar y analizar información de diagnóstico sobre la página. Una puntuación de 90 o más se considera buena. Una puntuación de 50 a 89 es una puntuación que necesita mejoras, y una puntuación inferior a 50 se considera baja.

Métricas
La categoría Rendimiento también muestra el rendimiento de la página en diferentes métricas, como las siguientes: Primer procesamiento de imagen con contenido, Procesamiento de imagen con contenido más grande, Índice de velocidad, Cambio de diseño acumulado, Tiempo de carga y Tiempo de bloqueo total.

Cada métrica recibe una puntuación y está etiquetada con un ícono:

Bien se indica con un círculo verde.
Necesita mejorar se indica con un cuadrado informativo ámbar
“Deficiente” se indica con un triángulo rojo de advertencia.
Auditorías
Dentro de cada categoría, se incluyen auditorías que proporcionan información para mejorar la experiencia del usuario de la página. Consulta la documentación de Lighthouse para obtener un desglose detallado de las auditorías de cada categoría.

Preguntas frecuentes
¿Qué condiciones de dispositivo y red usa Lighthouse para simular una carga de página?
Actualmente, Lighthouse simula las condiciones de carga de la página de un dispositivo de gama media (Moto G4). en una red móvil para dispositivos móviles y y emular una computadora de escritorio con una conexión por cable. PageSpeed también se ejecuta en un entorno que puede variar en función del estado de la red, verifica la ubicación fue observando el bloque de entorno de Lighthouse Report:

Captura de pantalla de la información sobre la herramienta de información de limitación.
Nota: PageSpeed informará que se ejecuta en una de las siguientes regiones: Norteamérica, Europa o Asia.

¿Por qué a veces los datos de campo y de lab se contradicen entre sí?
Los datos de campo son un informe histórico sobre el rendimiento de una URL en particular y representan datos de rendimiento anónimos de los usuarios en el mundo real en una variedad de dispositivos y condiciones de red. Los datos de lab se basan en una carga simulada de una página en un solo dispositivo y son fijas conjunto de condiciones de red. Por lo tanto, los valores pueden variar. Consulta Por qué los datos de campo y de laboratorio pueden ser diferentes (y qué hacer al respecto) para obtener más información.

¿Por qué se elige el percentil 75 para todas las métricas?
Nuestro objetivo es asegurarnos de que las páginas funcionen bien para la mayoría de los usuarios. Si nos enfocamos en los valores del percentil 75 de nuestras métricas, garantizamos que las páginas proporcionen una buena experiencia del usuario en las condiciones más difíciles de dispositivos y redes. Consulta Define los umbrales de métricas web esenciales. para obtener más información.

¿Cuál es una buena puntuación para los datos de lab?
Cualquier puntuación verde (90+) se considera buena, pero ten en cuenta que tener buenos datos de lab necesariamente significa que las experiencias de los usuarios reales también serán buenas.

¿Por qué la puntuación de rendimiento cambia de ejecución en ejecución? No hice ningún cambio en mi página.
La variabilidad en la medición del rendimiento se introduce a través de una serie de canales con diferentes niveles de impacto. Varias fuentes comunes de métricas la variabilidad son la disponibilidad de la red local, la disponibilidad de hardware del cliente y los recursos contención.

¿Por qué los datos de CrUX de usuarios reales no están disponibles para una URL o un origen?
El Informe sobre la experiencia del usuario en Chrome agrega datos de velocidad reales de usuarios que habilitaron la opción y requiere que una URL sea pública (rastreable y indexable) y tenga una cantidad suficiente de muestras distintas que proporcionen una vista representativa y anónima del rendimiento de la URL o el origen.

¿Tienes más dudas?
Si tienes una pregunta específica y que se pueda responder sobre el uso de PageSpeed Insights, hazla en inglés en Stack Overflow.

Si tienes comentarios o preguntas generales sobre PageSpeed Insights, o deseas iniciar una discusión general, inicia una conversación en la lista de distribución.

Si tienes preguntas generales sobre las métricas de las métricas web, inicia una conversación en el grupo de debate web-vitals-feedback.