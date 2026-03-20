# ESTÁNDAR GLOBAL DEL PROYECTO
# INSTRUCCIONES PARA EL AGENTE

Este archivo define las reglas del proyecto.
Siempre debes seguir estas reglas antes de generar código.
Si hay conflicto, este archivo tiene prioridad.

Proyecto: Sistema POS / ventas intercambios o ventas e intercambio negociaciones de servicios y articulos 

## Arquitectura
- MVC
- Controllers → solo reciben requests
- Services → lógica de negocio
- Models → acceso a datos

## Reglas obligatorias
- No lógica en controllers
- Validar inputs siempre
- Usar servicios para lógica
- Respuestas en JSON estándar

## Formato de respuesta
{
  "success": true,
  "data": null,
  "message": ""
}

## Seguridad
- Validar datos
- Evitar SQL Injection
- Sanitizar inputs