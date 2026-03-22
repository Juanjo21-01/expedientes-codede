  ---
  applyTo: "resources/views/**/*.blade.php"
  description: "UI Blade en Laravel 12 con Tailwind + daisyUI. Mejorar solo maquetación/UX sin tocar lógica, directivas ni bindings existentes."
  ---

  Contexto de aplicación:

  - Sistema empresarial para gestión de expedientes, guias, revisiones, usuarios y notificaciones.
  - Las vistas priorizan lectura rápida de estados, filtros operativos y acciones por rol.

  Para vistas Blade:

  - Usar Tailwind + daisyUI
  - No alterar lógica Blade existente
  - Mejorar únicamente UI/UX
  - Mantener semántica HTML clara y estructura limpia
  - Priorizar legibilidad y productividad del usuario final

  Componentes obligatorios:

  - Usar card para contenedores
  - Usar btn para acciones
  - Usar table para listados
  - Usar badge para estados
  - Usar alert para mensajes

  Componentes recomendados según escenario:

  - Formularios: input, select, textarea, checkbox, radio, toggle
  - Navegacion interna o secciones: tabs
  - Confirmaciones/diálogos: modal
  - Navegacion principal o lateral: navbar y drawer

  Patrones de maquetación esperados:

  - Encabezado de vista con titulo claro + acciones principales.
  - Zona de filtros en card compacta antes de los listados.
  - Tablas dentro de contenedor con overflow-x-auto en pantallas pequeñas.
  - Acciones de fila agrupadas, consistentes y con prioridad visual.
  - Vistas de detalle separadas en cards por bloques de informacion.

  Diseño:

  - Moderno, limpio, corporativo
  - Espaciado consistente (padding, margin)
  - Uso adecuado de colores y jerarquía visual
  - Contrastes correctos para lectura continua
  - Estados visuales consistentes para pendiente, aprobado, rechazado, inactivo, etc.

  Reglas de interacción:

  - La accion principal debe destacarse con btn-primary.
  - Acciones secundarias con estilos neutrales o outline.
  - Acciones destructivas con estilo de peligro y confirmación explicita.
  - Mensajes de éxito/error/advertencia con alert y texto concreto.

  Restricciones Laravel / Blade / Livewire:

  - No modificar lógica PHP embebida ni condiciones de negocio.
  - No romper directivas Blade (@if, @foreach, @can, @error, @csrf, @method, etc.).
  - No eliminar ni renombrar bindings existentes (wire:model, wire:click, wire:submit, x-data, x-show).
  - No cambiar nombres de rutas, variables o componentes.
  - Si hay modales con Livewire + Alpine, controlar apertura/cierre del lado cliente con clase dinámica ligada al estado.

  Responsive y accesibilidad:

  - Mobile-first obligatorio.
  - En móvil: acciones apiladas y tablas con desplazamiento horizontal controlado.
  - En desktop: aprovechar columnas sin perder legibilidad.
  - Estados hover/focus visibles en botones, links, inputs y controles.
  - Etiquetas y mensajes de ayuda claros para formularios.

  Evitar:

  - HTML desordenado
  - Exceso de divs
  - Clases duplicadas o innecesarias
  - Mezclar patrones visuales distintos en una misma vista
  - Efectos decorativos que resten claridad funcional

  Checklist rapido antes de cerrar cambios:

  - La vista conserva 100% su comportamiento original.
  - La estructura visual facilita entender estado, datos y accion.
  - La interfaz es consistente con el resto del sistema.
  - La vista funciona y se ve bien en móvil y escritorio.
