# Instrucciones para generar esquema MySQL

Generar las migrations, modelos y relaciones Laravel para una plataforma multi-organización de gestión de material coral.

Tecnologías:

- PHP 8
- Laravel
- MySQL
- Laravel Sanctum
- Laravel Storage

## Reglas generales

- Usar `BIGINT UNSIGNED AUTO_INCREMENT` como PK.
- Agregar `public_id UUID` único en entidades expuestas por API.
- Usar `created_at` y `updated_at`.
- Usar `deleted_at` o `archived_at` sólo donde se indique.
- Crear foreign keys e índices.
- No almacenar archivos como BLOB.
- No agregar `organization_id` directamente a `users`.
- Un usuario puede pertenecer a múltiples organizaciones.
- `role` y `voice_type` pertenecen a `organization_memberships`.
- Aplicar aislamiento multi-tenant por `organization_id`.
- Usar enums PHP o constantes para estados; en DB preferir `VARCHAR` para facilitar evolución.
- Generar migrations en orden correcto según dependencias.
- Generar relaciones Eloquent.
- Generar factories y seeders mínimos.
- No implementar UI.

---

## 1. users

Campos:

```text
id
public_id UUID UNIQUE
name VARCHAR(150)
email VARCHAR(255) UNIQUE
email_verified_at TIMESTAMP NULL
password VARCHAR(255)
photo_path VARCHAR(500) NULL
description VARCHAR(500) NULL
is_super_admin BOOLEAN DEFAULT false
status VARCHAR(30) DEFAULT 'ACTIVE'
remember_token
created_at
updated_at
deleted_at NULL
```

Estados:

```text
ACTIVE
SUSPENDED
```

---

## 2. organizations

Campos:

```text
id
public_id UUID UNIQUE
name VARCHAR(200)
description TEXT NULL
logo_path VARCHAR(500) NULL
owner_user_id FK users
status VARCHAR(30)
city VARCHAR(150) NULL
created_at
updated_at
archived_at TIMESTAMP NULL
```

Estados:

```text
ACTIVE
SUSPENDED
ARCHIVED
```

Índices:

```text
status
owner_user_id
name
```

---

## 3. organization_requests

Campos:

```text
id
public_id UUID UNIQUE
requested_by FK users
organization_name VARCHAR(200)
description TEXT NULL
city VARCHAR(150) NULL
additional_info TEXT NULL
status VARCHAR(30)
reviewed_by FK users NULL
reviewed_at TIMESTAMP NULL
review_notes TEXT NULL
organization_id FK organizations NULL
created_at
updated_at
```

Estados:

```text
PENDING
APPROVED
REJECTED
CANCELLED
```

---

## 4. organization_memberships

Campos:

```text
id
organization_id FK organizations
user_id FK users
role VARCHAR(30)
voice_type VARCHAR(30) NULL
status VARCHAR(30)
requested_at TIMESTAMP NULL
approved_at TIMESTAMP NULL
approved_by FK users NULL
joined_at TIMESTAMP NULL
left_at TIMESTAMP NULL
created_at
updated_at
```

Roles:

```text
ORG_ADMIN
MEMBER
```

Voice types:

```text
SOPRANO
ALTO
TENOR
BASS
```

Estados:

```text
PENDING
ACTIVE
REJECTED
SUSPENDED
LEFT
```

Restricción:

```text
UNIQUE(organization_id, user_id)
```

Índices:

```text
(user_id, status)
(organization_id, status)
(organization_id, role)
(organization_id, voice_type)
```

---

## 5. groups

Campos:

```text
id
public_id UUID UNIQUE
organization_id FK organizations
name VARCHAR(150)
description VARCHAR(500) NULL
status VARCHAR(30) DEFAULT 'ACTIVE'
created_by FK users
created_at
updated_at
archived_at TIMESTAMP NULL
```

Estados:

```text
ACTIVE
ARCHIVED
```

Restricción:

```text
UNIQUE(organization_id, name)
```

---

## 6. group_members

Campos:

```text
id
group_id FK groups
membership_id FK organization_memberships
created_at
```

Restricción:

```text
UNIQUE(group_id, membership_id)
```

Validar en aplicación que grupo y membership pertenezcan a la misma organización.

---

## 7. tags

Campos:

```text
id
public_id UUID UNIQUE
name VARCHAR(100) UNIQUE
slug VARCHAR(120) UNIQUE
status VARCHAR(30) DEFAULT 'ACTIVE'
created_by FK users
created_at
updated_at
```

Estados:

```text
ACTIVE
INACTIVE
```

---

## 8. pieces

Campos:

```text
id
public_id UUID UNIQUE
organization_id FK organizations
title VARCHAR(100)
subtitle VARCHAR(250) NULL
body TEXT NULL
status VARCHAR(30) DEFAULT 'ACTIVE'
created_by FK users
updated_by FK users NULL
published_at TIMESTAMP NULL
created_at
updated_at
archived_at TIMESTAMP NULL
```

Estados:

```text
ACTIVE
ARCHIVED
```

Índices:

```text
(organization_id, status)
(organization_id, title)
published_at
```

---

## 9. piece_tags

Campos:

```text
piece_id FK pieces
tag_id FK tags
created_at
```

Restricción:

```text
UNIQUE(piece_id, tag_id)
```

---

## 10. piece_files

Usar una sola tabla para PDF y audios.

Campos:

```text
id
public_id UUID UNIQUE
piece_id FK pieces
file_type VARCHAR(30)
voice_type VARCHAR(30)
storage_disk VARCHAR(50)
storage_path VARCHAR(500)
original_filename VARCHAR(255)
mime_type VARCHAR(100)
file_size BIGINT UNSIGNED
duration_seconds INT UNSIGNED NULL
checksum VARCHAR(128) NULL
created_by FK users
created_at
updated_at
```

Valores `file_type`:

```text
SCORE
AUDIO
```

Valores `voice_type`:

```text
GENERAL
SOPRANO
ALTO
TENOR
BASS
```

Reglas:

```text
SCORE -> voice_type GENERAL
AUDIO -> SOPRANO | ALTO | TENOR | BASS
```

Restricción:

```text
UNIQUE(piece_id, file_type, voice_type)
```

Esto garantiza:

- máximo 1 PDF;
- máximo 1 audio por cuerda.

Validaciones de aplicación:

```text
PDF máximo 5 MB
MP3/WAV máximo 5 MB
```

No almacenar contenido binario en MySQL.

---

## 11. piece_shares

Campos:

```text
id
piece_id FK pieces
share_type VARCHAR(30)
voice_type VARCHAR(30) NULL
group_id FK groups NULL
membership_id FK organization_memberships NULL
created_by FK users
created_at
```

Valores:

```text
ORGANIZATION
VOICE
GROUP
MEMBER
```

Reglas:

```text
ORGANIZATION -> sin voice_type, group_id ni membership_id
VOICE -> requiere voice_type
GROUP -> requiere group_id
MEMBER -> requiere membership_id
```

Validar en aplicación que grupo o membership pertenezcan a la misma organización de la pieza.

Índices:

```text
(piece_id, share_type)
voice_type
group_id
membership_id
```

---

## 12. favorites

Campos:

```text
id
user_id FK users
piece_id FK pieces
created_at
```

Restricción:

```text
UNIQUE(user_id, piece_id)
```

---

## 13. piece_views

Campos:

```text
id
user_id FK users
piece_id FK pieces
first_viewed_at TIMESTAMP
last_viewed_at TIMESTAMP
view_count INT UNSIGNED DEFAULT 1
created_at
updated_at
```

Restricción:

```text
UNIQUE(user_id, piece_id)
```

---

## 14. notification_preferences

Campos:

```text
id
user_id FK users UNIQUE
new_piece BOOLEAN DEFAULT true
voice_audio_added BOOLEAN DEFAULT true
membership_changes BOOLEAN DEFAULT true
administrative_events BOOLEAN DEFAULT true
created_at
updated_at
```

---

## 15. notifications

Usar la tabla estándar de Laravel Notifications.

No crear diseño propio si Laravel ya la proporciona.

---

## 16. device_tokens

Preparar para futura app iOS.

Campos:

```text
id
user_id FK users
platform VARCHAR(20)
device_token VARCHAR(512) UNIQUE
device_name VARCHAR(150) NULL
last_seen_at TIMESTAMP NULL
created_at
updated_at
```

Valor inicial:

```text
IOS
```

---

## 17. reports

Campos:

```text
id
public_id UUID UNIQUE
reporter_user_id FK users
organization_id FK organizations
target_type VARCHAR(30)
target_id BIGINT UNSIGNED
reason VARCHAR(100)
description TEXT NULL
status VARCHAR(30)
resolved_by FK users NULL
resolved_at TIMESTAMP NULL
resolution_notes TEXT NULL
created_at
updated_at
```

Target types:

```text
USER
PIECE
```

Estados:

```text
OPEN
IN_REVIEW
RESOLVED
DISMISSED
```

---

## 18. audit_logs

Campos:

```text
id
user_id FK users NULL
organization_id FK organizations NULL
action VARCHAR(100)
entity_type VARCHAR(100)
entity_id BIGINT UNSIGNED NULL
old_values JSON NULL
new_values JSON NULL
ip_address VARCHAR(45) NULL
user_agent VARCHAR(500) NULL
created_at
```

No guardar contraseñas, tokens ni secretos.

---

## 19. Tablas Laravel estándar

Mantener compatibilidad con:

```text
password_reset_tokens
sessions
personal_access_tokens
notifications
```

Usar Laravel Sanctum para tokens de API.

---

## 20. Relaciones Eloquent requeridas

Generar al menos:

```text
User
- memberships
- organizationRequests
- favorites
- pieceViews

Organization
- memberships
- groups
- pieces
- owner

OrganizationMembership
- user
- organization
- groups

Group
- organization
- memberships

Piece
- organization
- files
- tags
- shares
- favorites
- views

PieceFile
- piece

Tag
- pieces
```

---

## 21. Reglas de integridad

Implementar validaciones para evitar relaciones entre organizaciones distintas.

Casos obligatorios:

```text
group.organization_id == membership.organization_id

piece.organization_id == group.organization_id

piece.organization_id == membership.organization_id
```

No confiar en IDs enviados por frontend.

---

## 22. Seeders mínimos

Crear:

```text
1 super admin
2 organizaciones
2 org admins
8 members
4 voice types distribuidos
3 grupos
6 tags
10 pieces
varias reglas piece_shares
```

Generar datos para poder comprobar acceso entre organizaciones.

---

## 23. Resultado esperado

Generar:

```text
migrations
models
relationships
factories
seeders
```

No generar todavía:

```text
controllers
views
Livewire components
API endpoints
business services
```

La prioridad de esta tarea es dejar el esquema MySQL y las relaciones Eloquent correctamente construidas.