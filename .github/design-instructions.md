Este proyecto utiliza Laravel 12 con Blade (y componentes Livewire en varios módulos).

Objetivo de estas instrucciones:

- Guiar a cualquier skill de UI para maquetar con criterio consistente.
- Mejorar interfaz y experiencia sin alterar lógica funcional.
- Mantener una estética empresarial moderna, limpia y profesional.

Reglas técnicas obligatorias:

- Usar exclusivamente Tailwind CSS + daisyUI.
- No usar Bootstrap ni otros frameworks CSS.
- No usar estilos inline (style="") salvo casos excepcionales y justificados.
- No introducir librerías visuales nuevas sin necesidad real del módulo.

Componentes daisyUI que se deben priorizar:

- Contenedores y bloques: card.
- Acciones: btn.
- Listados de datos: table.
- Estados y categorías: badge.
- Mensajes de sistema: alert.
- Diálogos y confirmaciones: modal.
- Navegación superior: navbar.
- Navegación lateral: drawer.
- Secciones de navegación interna: tabs.
- Formularios: input, select, textarea, checkbox, radio, toggle.

Contexto funcional del sistema (para mejor criterio visual):

- Módulos centrales: expedientes, guías, revisiones financieras, usuarios, municipios y notificaciones.
- Patrones comunes de pantalla: listados con filtros, formularios de captura/edición, vistas de detalle y bitácoras.
- Hay jerarquía por roles y estados del proceso, por lo tanto el diseño debe facilitar lectura de estado, prioridad y acción disponible.

Lineamientos de diseño:

- Evitar diseños genéricos o básicos sin identidad.
- Definir jerarquía visual clara entre título, subtítulo, métrica, contenido y acciones.
- Usar espaciado consistente y respiración visual en todo el sistema.
- Respetar una paleta corporativa coherente y contrastes funcionales.
- Agrupar información por bloques semánticos (cards) y no por divs sin estructura.

Lineamientos de interacción:

- Botón primario: acción principal de la vista (guardar, aprobar, crear).
- Botones secundarios: acciones de soporte (cancelar, volver, limpiar).
- Acciones críticas (eliminar, rechazar) con estilo de peligro y confirmación clara.
- Los mensajes de éxito, error o advertencia deben mostrarse con alert y texto directo.

Laravel / Blade / Livewire (restricciones fuertes):

- NO modificar lógica PHP.
- NO alterar consultas, controladores, modelos, policies ni validaciones.
- NO romper directivas Blade (@if, @foreach, @can, @error, etc.).
- NO eliminar ni renombrar wire:model, wire:click, wire:submit, x-data u otros bindings ya funcionales.
- Respetar layouts, secciones y componentes Blade existentes.
- Si hay modales Livewire + Alpine, mantener apertura/cierre en cliente con clases dinámicas de Alpine ligadas al estado del componente.

Responsive:

- Todo debe ser responsive con enfoque mobile-first.
- En móviles: priorizar legibilidad, scroll horizontal controlado en tablas y acciones apiladas.
- En escritorio: aprovechar rejilla para densidad de información sin saturar.

Accesibilidad mínima obligatoria:

- Buen contraste entre fondo, texto y estados.
- Estados hover/focus visibles y consistentes.
- Etiquetas claras en formularios y ayudas de validación visibles.
- Texto de botones y acciones con verbos explícitos.

Evitar siempre:

- HTML desordenado o con profundidad innecesaria.
- Clases utilitarias duplicadas sin aporte.
- Cambios visuales que oculten informacion importante del negocio.
- Efectos decorativos que degraden claridad, rendimiento o usabilidad.

Checklist de calidad antes de dar por terminada una vista:

- La vista usa componentes daisyUI correctos para su tipo de contenido.
- Se conserva toda la funcionalidad Blade/Livewire.
- La jerarquía visual permite escanear rápido estado, datos y acciones.
- La vista es usable en móvil y escritorio.
- Los mensajes y estados son claros, accesibles y consistentes.
