# Instrucciones UI/UX para Codex

Implementar una interfaz web limpia, minimalista, moderna y responsive para la plataforma coral.

Usar la tecnología frontend ya definida en el proyecto. No introducir un framework adicional sólo por diseño.

## Objetivos visuales

- Diseño sobrio y claro.
- Priorizar legibilidad.
- Evitar exceso de colores.
- Evitar sombras fuertes.
- Evitar bordes innecesarios.
- Usar buen espacio en blanco.
- Navegación simple.
- Jerarquía visual clara.
- Optimizar especialmente para móvil.
- Mantener consistencia entre área pública, MEMBER, ORG_ADMIN y SUPER_ADMIN.

## Estilo general

Usar:

- fondo claro;
- superficies blancas o neutras;
- tipografía sans-serif moderna;
- bordes suaves;
- radios moderados;
- iconografía simple;
- botones claramente identificables;
- estados hover/focus accesibles;
- diseño visual sin elementos decorativos innecesarios.

No usar:

- gradientes excesivos;
- glassmorphism;
- animaciones llamativas;
- dashboards saturados;
- cards para absolutamente todo;
- colores distintos para cada módulo;
- tablas gigantes en móvil.

Priorizar una apariencia similar a una herramienta moderna de productividad.

---

# 1. Responsive

Diseñar mobile-first.

Breakpoints mínimos:

```text
mobile
tablet
desktop
```

Todo debe funcionar correctamente desde aproximadamente:

```text
320px → escritorio
```

No permitir scroll horizontal general de página.

En móvil:

- navegación colapsable;
- botones con área táctil cómoda;
- formularios a una columna;
- tablas transformadas en listas/cards cuando sea necesario;
- acciones secundarias dentro de menú contextual;
- filtros colapsables;
- PDFs y reproductores adaptados al ancho disponible.

---

# 2. Landing page pública

Crear una landing page accesible sin autenticación.

Ruta sugerida:

```text
/
```

Objetivo:

explicar brevemente la utilidad de la plataforma y permitir acceder o registrarse.

La landing page debe incluir sólo estas secciones.

## Header

Mostrar:

- logo/nombre de la plataforma;
- enlace "Ingresar";
- botón "Crear cuenta".

En móvil usar navegación compacta.

---

## Hero

Contenido:

```text
Título principal
Tu repertorio coral, siempre disponible.

Texto
Partituras, audios de ensayo y material del coro organizados en un solo lugar.

CTA principal
Crear cuenta

CTA secundario
Ingresar
```

Agregar una ilustración simple o mockup de la aplicación sólo si ya existen recursos adecuados.

No depender de imágenes externas.

---

## Beneficios

Mostrar máximo 4 beneficios.

Ejemplo:

```text
Partituras organizadas
Encuentra rápidamente el material del coro.

Audios por cuerda
Practica Soprano, Alto, Tenor o Bajo.

Acceso privado
El contenido sólo está disponible para miembros autorizados.

Disponible donde estés
Accede desde computador o teléfono.
```

---

## Cómo funciona

Máximo tres pasos:

```text
1. Únete a tu coro
2. Encuentra tu repertorio
3. Practica con tu partitura y audio
```

---

## Footer

Incluir:

- Acerca de;
- Privacidad;
- Términos;
- Contacto;
- copyright.

No agregar secciones comerciales innecesarias.

---

# 3. Página de login

Crear una pantalla simple.

Desktop:

```text
-----------------------------------------
|                                       |
|           Nombre / Logo               |
|                                       |
|           Iniciar sesión              |
|                                       |
| Email                                 |
| [.................................]   |
|                                       |
| Contraseña                            |
| [.................................]   |
|                                       |
| [ Ingresar ]                          |
|                                       |
| ¿Olvidaste tu contraseña?             |
|                                       |
| ¿No tienes cuenta? Crear cuenta       |
|                                       |
-----------------------------------------
```

En móvil ocupar casi todo el ancho disponible con márgenes pequeños.

No agregar paneles gráficos innecesarios.

---

# 4. Registro

Formulario simple:

```text
Nombre
Email
Contraseña
Confirmar contraseña
```

Luego del registro permitir:

```text
Solicitar ingreso a organización
o
Solicitar creación de organización
```

No incluir estas decisiones dentro del formulario inicial de registro.

---

# 5. Layout autenticado

Desktop:

```text
┌──────────────────────────────────────────────┐
│ Header                                       │
├──────────────┬───────────────────────────────┤
│ Sidebar      │ Contenido                     │
│              │                               │
│ Biblioteca   │                               │
│ Favoritos    │                               │
│ Grupos       │                               │
│ ...          │                               │
└──────────────┴───────────────────────────────┘
```

Móvil:

```text
Header
☰     Organización                 Perfil
-------------------------------------------
Contenido
```

La sidebar debe convertirse en drawer/menu móvil.

---

# 6. Header autenticado

Incluir:

- organización actual;
- selector de organización si pertenece a varias;
- notificaciones;
- avatar/perfil.

No saturar con acciones administrativas.

---

# 7. Navegación MEMBER

Mostrar únicamente:

```text
Biblioteca
Favoritos
Notificaciones
Perfil
```

Opcional:

```text
Mis grupos
```

si aporta información útil.

No mostrar elementos administrativos ocultos/deshabilitados.

---

# 8. Navegación ORG_ADMIN

Agregar:

```text
Biblioteca
Piezas
Miembros
Solicitudes
Grupos
Organización
```

Mantener separadas las opciones de administración de las opciones personales.

---

# 9. Navegación SUPER_ADMIN

Incluir:

```text
Dashboard
Organizaciones
Solicitudes
Usuarios
Etiquetas
Reportes
```

---

# 10. Biblioteca

La biblioteca es la pantalla principal del MEMBER.

Parte superior:

```text
Mi repertorio

[ Buscar por título, subtítulo o etiqueta ]

[Filtros]
Todos | Nuevos | Favoritos
```

En desktop se puede usar grid o lista.

Preferencia:

```text
lista limpia
```

Cada pieza debe mostrar:

```text
Título
Subtítulo
Etiquetas
Indicador NUEVO
Favorito
```

Ejemplo:

```text
Ave Maria                          ☆
Franz Schubert

Clásica · Sacra          NUEVO
```

No mostrar metadatos técnicos.

---

# 11. Búsqueda

La búsqueda debe:

- funcionar sin recargar página cuando sea razonable;
- aceptar título;
- subtítulo;
- etiquetas;
- tener debounce si se implementa búsqueda dinámica;
- mostrar estado vacío.

Ejemplo:

```text
No encontramos piezas para "mozart".
```

---

# 12. Filtros

En desktop:

```text
[Todos] [Nuevos] [Favoritos] [Etiquetas ▼]
```

En móvil:

```text
[Filtros]
```

abrirá panel, drawer o dropdown.

Evitar ocupar varias filas con filtros.

---

# 13. Detalle de pieza

Diseño recomendado:

```text
← Biblioteca

Ave Maria                     ☆
Franz Schubert

Clásica · Sacra

Texto descriptivo...


Partitura
────────────────────
[ Ver partitura ]
[ Imprimir ]


Audios
────────────────────

Mi voz
TENOR

▶  ━━━━━━━━━━━━━━━  02:31

Otras voces

Soprano            ▶
Alto               ▶
Bajo               ▶
```

Priorizar visualmente la pista correspondiente a la cuerda del usuario.

---

# 14. Reproductor

Implementar controles simples:

```text
play/pause
barra de progreso
tiempo actual
duración
```

Si es simple de implementar:

```text
-10 segundos
+10 segundos
```

No agregar ecualizador, waveform ni funciones avanzadas.

En móvil asegurar controles grandes y táctiles.

---

# 15. Visor PDF

Desktop:

- PDF embebido cuando el navegador lo permita;
- botón para abrir;
- botón imprimir.

Móvil:

- visor adaptado al ancho;
- evitar controles duplicados;
- permitir abrir la vista completa.

---

# 16. Favoritos

Usar icono simple de estrella o equivalente.

Estados:

```text
☆ no favorito
★ favorito
```

Debe poder alternarse directamente desde:

- biblioteca;
- detalle.

Proporcionar feedback inmediato.

---

# 17. Estados NUEVO

Usar una badge pequeña:

```text
Nuevo
```

No utilizar colores agresivos.

Desaparecer después de que la pieza haya sido abierta según la lógica definida.

---

# 18. Piezas — ORG_ADMIN

Pantalla:

```text
Piezas

[ Buscar........................ ] [ Nueva pieza ]

Título              Etiquetas         Estado     Acciones
```

En móvil transformar cada fila en bloque:

```text
Ave Maria
Clásica · Sacra
Activo

[Editar] [•••]
```

Acciones secundarias:

```text
Archivar
Compartir
```

dentro de menú contextual.

---

# 19. Crear/editar pieza

Dividir el formulario visualmente en secciones.

```text
Información

Título
Subtítulo
Texto
Etiquetas


Partitura

PDF
[ seleccionar archivo ]


Audios

Soprano [ archivo ]
Alto    [ archivo ]
Tenor   [ archivo ]
Bajo    [ archivo ]


Compartir con

○ Toda la organización

□ Soprano
□ Alto
□ Tenor
□ Bajo

Grupos
[...]

Usuarios
[...]


[ Guardar ]
```

En móvil todo debe ser vertical.

No usar wizard salvo que el formulario resulte excesivamente largo.

---

# 20. Miembros

Desktop:

```text
Nombre
Email
Cuerda
Rol
Estado
Acciones
```

Agregar:

```text
Buscar
Filtrar por cuerda
Filtrar por estado
```

Móvil:

```text
Pedro González
pedro@email.com

TENOR · MEMBER
Activo

[•••]
```

---

# 21. Solicitudes

Mostrar claramente:

```text
Pedro González
Solicita ingresar al coro

[ Aprobar ] [ Rechazar ]
```

Evitar tabla si hay pocas solicitudes.

---

# 22. Grupos

Listado:

```text
Concierto Navidad
12 miembros

Principiantes
8 miembros
```

Acciones:

```text
Editar
Miembros
Archivar
```

---

# 23. Perfil

Pantalla simple:

```text
Foto

Nombre
Descripción
Email

[ Guardar cambios ]
```

Sección adicional:

```text
Seguridad
Cambiar contraseña
```

y:

```text
Cuenta
Eliminar cuenta
```

La eliminación debe aparecer separada visualmente y requerir confirmación.

---

# 24. Notificaciones

Listado cronológico.

Ejemplo:

```text
Nueva pieza disponible

Ave Maria fue agregada a tu repertorio.

Hace 2 horas
```

Diferenciar:

```text
leída
no leída
```

sin usar exceso de color.

---

# 25. Estados vacíos

Crear estados vacíos útiles.

Ejemplos:

```text
Todavía no tienes piezas disponibles.
```

```text
No hay solicitudes pendientes.
```

```text
Aún no has marcado favoritos.
```

Cuando corresponda y el usuario tenga permisos, agregar CTA.

---

# 26. Feedback de acciones

Usar mensajes breves:

```text
Pieza guardada.
Miembro aprobado.
Cambios realizados.
```

Errores:

```text
No pudimos guardar los cambios.
```

Mostrar errores específicos de validación junto al campo correspondiente.

---

# 27. Confirmaciones

Solicitar confirmación únicamente para acciones relevantes:

```text
archivar pieza
suspender miembro
rechazar solicitud
eliminar cuenta
```

No solicitar confirmación para acciones reversibles como:

```text
favorito
filtros
búsqueda
```

---

# 28. Accesibilidad

Implementar:

- labels asociados a inputs;
- navegación por teclado;
- focus visible;
- contraste suficiente;
- texto alternativo en imágenes;
- botones con nombres accesibles;
- no depender sólo del color para comunicar estados;
- HTML semántico.

---

# 29. Componentes reutilizables

Crear componentes sólo cuando exista reutilización real.

Ejemplos:

```text
Button
Input
Select
Textarea
Badge
Modal
EmptyState
FlashMessage
PieceListItem
AudioPlayer
Pagination
```

No crear un design system complejo.

---

# 30. Eficiencia de implementación

Codex debe:

1. reutilizar componentes existentes;
2. evitar duplicar markup;
3. no introducir librerías UI completas salvo que ya estén instaladas;
4. no agregar JavaScript donde HTML/Livewire/CSS sea suficiente;
5. evitar animaciones innecesarias;
6. no modificar lógica de negocio al trabajar en UI;
7. mantener vistas pequeñas;
8. extraer componentes sólo cuando simplifique el código;
9. verificar cada pantalla en desktop y móvil;
10. no rehacer pantallas ya funcionales sin necesidad.

---

# 31. Prioridad de implementación UI

Implementar en este orden:

```text
1. Layout público
2. Landing page
3. Login
4. Registro
5. Layout autenticado responsive
6. Biblioteca
7. Detalle de pieza
8. Perfil
9. Organización
10. Miembros
11. Solicitudes
12. Grupos
13. Piezas admin
14. Crear/editar pieza
15. Etiquetas
16. Reportes
17. Notificaciones
18. Estados vacíos/error/loading
19. revisión responsive completa
20. accesibilidad
```

---

# 32. Criterio final

La interfaz se considera correcta cuando:

- puede usarse cómodamente desde iPhone;
- no requiere zoom horizontal;
- las acciones principales se encuentran rápidamente;
- MEMBER no ve complejidad administrativa;
- ORG_ADMIN puede administrar sin pantallas saturadas;
- SUPER_ADMIN tiene acceso claro a supervisión;
- la landing explica el producto en pocos segundos;
- abrir una pieza y reproducir el audio correspondiente requiere el mínimo número de acciones posible.