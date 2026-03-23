---
applyTo: "{app,routes,database,tests,config}/**/*.php"
description: "Revisar proyecto Laravel para detectar errores, malas practicas, mejoras de arquitectura y refactorizar sin perder funcionalidades ni enfoque del negocio. Incluir limpieza de codigo no funcional y agregar codigo faltante de forma segura."
---

Contexto de esta instrucción:

- Proyecto Laravel 12 y Livewire v4 con Blade y módulos de negocio en producción funcional.
- Esta instrucción se usa cuando se pida revisar, auditar, refactorizar o limpiar código backend.
- El objetivo es mejorar calidad técnica sin cambiar la intención funcional del sistema.

Objetivo principal:

- Detectar errores reales y riesgos técnicos.
- Proponer y aplicar mejoras de mantenibilidad, legibilidad y rendimiento.
- Eliminar código no funcional o muerto solo cuando haya evidencia.
- Agregar código faltante solo cuando sea necesario para estabilidad, seguridad o coherencia.

Regla de oro:

- No romper funcionalidades existentes ni cambiar reglas de negocio.

Flujo obligatorio para auditoria/refactor:

1. Analizar primero sin editar:

- Ubicar módulos/archivos impactados.
- Revisar errores de IDE, referencias rotas, imports sin uso y duplicaciones.
- Buscar riesgos comunes: N+1, validación incompleta, lógica de negocio en controladores, null handling deficiente.

2. Reportar hallazgos por severidad:

- Critico: rompe flujo, datos o seguridad.
- Alto: riesgo funcional, inconsistencia de negocio o deuda técnica peligrosa.
- Medio: mantenibilidad/performance mejorable.
- Bajo: limpieza, estilo, simplificación.

3. Proponer plan corto y ejecutar incrementalmente:

- Cambios pequeños, trazables y con bajo riesgo.
- Mantener nombres de rutas, contratos públicos y comportamiento observable.

4. Validar después de cada bloque:

- Ejecutar pruebas disponibles relacionadas.
- Verificar que no haya nuevos errores de análisis estático o lint.

Reglas técnicas Laravel (aplicar cuando corresponda):

- Usar tipado explicito en métodos y propiedades cuando sea seguro.
- Priorizar relaciones Eloquent y eager loading para evitar N+1.
- Mantener validaciones claras y centralizadas (Form Request o reglas consistentes).
- Evitar lógica de negocio pesada en controladores.
- Reutilizar servicios/acciones existentes antes de crear nuevas capas.
- Mantener compatibilidad con Livewire: no renombrar bindings ni eventos sin ajuste total.

Codigo no funcional (dead code):

- Solo eliminar si no tiene referencias de uso comprobables.
- Si hay duda de uso indirecto, no eliminar automáticamente; marcar como candidato y consultar.
- Al eliminar, limpiar también imports, métodos y ramas condicionales huérfanas relacionadas.

Codigo faltante (missing code):

- Agregar guard clauses, validaciones o manejo de errores cuando exista riesgo real.
- Completar tipado, retornos o null-safety donde falte y provoque fragilidad.
- Agregar pruebas mínimas para cubrir correcciones funcionales o de regresión.

No hacer:

- No reescribir módulos completos si basta refactor incremental.
- No introducir librerías nuevas sin justificación técnica clara.
- No cambiar estructura de base de datos sin requerimiento explicito.
- No modificar UI/maquetación en solicitudes enfocadas a backend.

Entrega esperada en cada solicitud:

1. Hallazgos priorizados con archivo y causa.
2. Plan de cambios con riesgo estimado.
3. Cambios aplicados y razon técnica.
4. Validaciones ejecutadas (tests/lint/errores IDE) y resultado.
5. Riesgos residuales y siguientes pasos opcionales.

Checklist de cierre:

- Se conserva el comportamiento funcional esperado.
- Se redujo deuda técnica sin sobre-ingeniería.
- No hay nuevos errores introducidos por el refactor.
- Los cambios son consistentes con convenciones Laravel del proyecto.
