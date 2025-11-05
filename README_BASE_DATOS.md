# Sistema de Gestión de Biblioteca - LGI

## 📋 Resumen del Proyecto

Sistema web para gestionar préstamos de libros en una biblioteca. Incluye control de inventario, usuarios/socios y registro completo de préstamos.

## 🗄️ Estructura de Base de Datos

### Tablas Principales

#### 1. **libros**
Catálogo de libros de la biblioteca.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT(11) | ID único (PK, Auto-increment) |
| titulo | VARCHAR(200) | Título del libro (NOT NULL) |
| subtitulo | VARCHAR(500) | Subtítulo |
| autor | VARCHAR(150) | Autor (NOT NULL) |
| isbn | VARCHAR(20) | ISBN único (UNIQUE, NOT NULL) |
| editorial | VARCHAR(100) | Editorial |
| ano_publicacion | YEAR | Año de publicación |
| paginas | INT(11) | Número de páginas |
| categoria | VARCHAR(50) | Categoría del libro |
| descripcion | TEXT | Sinopsis/descripción |
| stock | INT(11) | Cantidad de copias |
| ubicacion | VARCHAR(200) | Ubicación física |
| estado | ENUM | 'disponible' o 'prestado' |
| activo | TINYINT(1) | 1=activo, 0=inactivo |
| fecha_registro | TIMESTAMP | Fecha de alta |
| updated_at | TIMESTAMP | Última actualización |

**Índices:**
- `idx_libro_titulo` en titulo
- `idx_libro_autor` en autor
- `idx_libro_isbn` en isbn
- `idx_libro_categoria` en categoria
- `idx_libro_estado` en estado

---

#### 2. **usuarios**
Socios/usuarios que piden libros prestados (NO confundir con usuarios_sistema).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT(11) | ID único (PK, Auto-increment) |
| nombre_completo | VARCHAR(150) | Nombre completo (NOT NULL) |
| usuario | VARCHAR(100) | Usuario para login (UNIQUE) |
| email | VARCHAR(100) | Email (UNIQUE, NOT NULL) |
| password | VARCHAR(255) | Contraseña hash |
| telefono | VARCHAR(20) | Teléfono de contacto |
| direccion | VARCHAR(200) | Dirección |
| dni | VARCHAR(20) | DNI/Documento (UNIQUE, NOT NULL) |
| fecha_registro | DATE | Fecha de registro |
| estado | ENUM | 'activo' o 'suspendido' |
| rol | ENUM | 'usuario', 'bibliotecario', 'admin' |
| activo | TINYINT(1) | 1=activo, 0=inactivo |
| created_at | TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | Última actualización |

**Índices:**
- `idx_usuario_email` en email
- `idx_usuario_dni` en dni
- `idx_usuario_estado` en estado

---

#### 3. **prestamos**
Registro de todos los préstamos de libros.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT(11) | ID único (PK, Auto-increment) |
| libro_id | INT(11) | ID del libro (FK → libros.id) |
| usuario_id | INT(11) | ID del usuario (FK → usuarios.id) |
| fecha_prestamo | DATE | Fecha del préstamo |
| fecha_devolucion | DATE | Fecha esperada de devolución |
| fecha_dev_real | DATE | Fecha real de devolución (NULL si activo) |
| estado | ENUM | 'activo', 'devuelto', 'vencido' |
| observaciones | TEXT | Notas adicionales |
| created_at | TIMESTAMP | Fecha de creación |

**Claves Foráneas:**
- `libro_id` → `libros.id` (RESTRICT/CASCADE)
- `usuario_id` → `usuarios.id` (RESTRICT/CASCADE)

**Índices:**
- `idx_prestamo_libro` en libro_id
- `idx_prestamo_usuario` en usuario_id
- `idx_prestamo_estado` en estado
- `idx_prestamo_fecha_devolucion` en fecha_devolucion

---

#### 4. **usuarios_sistema**
Usuarios que operan el sistema (bibliotecarios/admins).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT(11) | ID único (PK, Auto-increment) |
| usuario | VARCHAR(50) | Usuario (UNIQUE, NOT NULL) |
| password | VARCHAR(255) | Hash de contraseña (NOT NULL) |
| nombre | VARCHAR(100) | Nombre completo (NOT NULL) |
| email | VARCHAR(100) | Email (UNIQUE, NOT NULL) |
| rol | ENUM | 'admin' o 'bibliotecario' |
| created_at | TIMESTAMP | Fecha de creación |

---

#### 5. **categorias** (Opcional - Funcionalidad extra)
Categorías de libros.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT(11) | ID único (PK, Auto-increment) |
| nombre | VARCHAR(100) | Nombre (UNIQUE, NOT NULL) |
| descripcion | TEXT | Descripción |
| activo | TINYINT(1) | 1=activa, 0=inactiva |
| created_at | TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | Última actualización |

---

#### 6. **configuracion**
Configuraciones del sistema.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT(11) | ID único (PK, Auto-increment) |
| clave | VARCHAR(100) | Clave config (UNIQUE, NOT NULL) |
| valor | TEXT | Valor de la configuración |
| descripcion | VARCHAR(255) | Descripción |
| tipo | ENUM | 'texto', 'numero', 'fecha', 'booleano' |
| actualizado_por | INT(11) | ID usuario que actualizó (FK) |
| fecha_actualizacion | TIMESTAMP | Última actualización |

**Configuraciones por defecto:**
- `dias_prestamo`: 14 días
- `max_prestamos_usuario`: 3 préstamos simultáneos
- `multa_dia_atraso`: $50 por día
- `dias_alerta_vencimiento`: 3 días antes de vencer

---

#### 7. **logs_sistema**
Registro de acciones en el sistema (auditoría).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT(11) | ID único (PK, Auto-increment) |
| usuario_id | INT(11) | ID del usuario (FK) |
| accion | VARCHAR(100) | Tipo de acción realizada |
| tabla | VARCHAR(50) | Tabla afectada |
| registro_id | INT(11) | ID del registro afectado |
| descripcion | TEXT | Descripción detallada |
| ip_address | VARCHAR(45) | IP del usuario |
| created_at | TIMESTAMP | Fecha/hora de la acción |

---

## 📊 Diagrama de Relaciones

```
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│     LIBROS      │         │    PRESTAMOS     │         │    USUARIOS     │
├─────────────────┤         ├──────────────────┤         ├─────────────────┤
│ id (PK)         │◄────────│ libro_id (FK)    │         │ id (PK)         │
│ titulo          │         │ usuario_id (FK)  │────────►│ nombre_completo │
│ autor           │         │ fecha_prestamo   │         │ email           │
│ isbn (UNIQUE)   │         │ fecha_devolucion │         │ dni (UNIQUE)    │
│ estado          │         │ fecha_dev_real   │         │ estado          │
└─────────────────┘         │ estado           │         └─────────────────┘
                            └──────────────────┘
                                      
┌─────────────────────┐
│  USUARIOS_SISTEMA   │  (Diferentes de usuarios)
├─────────────────────┤
│ id (PK)             │
│ usuario (UNIQUE)    │
│ password (hash)     │
│ rol                 │
└─────────────────────┘
```

---

## 🔑 Credenciales de Acceso

### ⚠️ IMPORTANTE - Seguridad en Desarrollo
Este proyecto usa **contraseñas en texto plano** para facilitar el desarrollo y pruebas.  
**NUNCA uses esto en producción**. En producción debes usar `password_hash()` y `password_verify()`.

### Usuarios del Sistema (Login)
- **Admin:** `admin` / `admin123`
- **Bibliotecario:** `biblio` / `biblio123`

### Usuarios/Socios (Ejemplo)
- Cualquier usuario registrado puede iniciar sesión
- Contraseña de prueba: `user123`
- Ejemplos: `jperez`, `mgonzalez`, `crodriguez`, etc.

---

## 🚀 Instalación

1. **Importar Base de Datos:**
   ```bash
   mysql -u root libreria < sql/estructura_completa.sql
   mysql -u root libreria < sql/insertar_datos_basicos.sql
   ```

2. **Configurar Conexión:**
   Editar `config/database.php` con tus credenciales:
   ```php
   $host = 'localhost';
   $dbname = 'libreria';
   $username = 'root';
   $password = '';
   ```

3. **Acceder al Sistema:**
   - URL: `http://localhost/ProyectoLibreriaLGI`
   - Login con credenciales de admin

---

## ✅ Reglas de Negocio Implementadas

### Al Registrar un Préstamo:
1. ✅ El libro debe estar **disponible**
2. ✅ El usuario debe estar **activo** (no suspendido)
3. ✅ El usuario NO debe tener préstamos **vencidos**
4. ✅ El usuario NO debe exceder el límite de **3 préstamos simultáneos**
5. ✅ Fecha de devolución automática: **fecha_prestamo + 14 días**

### Al Registrar una Devolución:
1. ✅ Actualiza `fecha_dev_real` con la fecha actual
2. ✅ Cambia `estado` del préstamo a **'devuelto'**
3. ✅ Cambia `estado` del libro a **'disponible'**
4. ✅ Calcula días de atraso si aplica

### Al Eliminar un Libro:
1. ❌ **NO** se puede eliminar si tiene préstamos activos
2. ⚠️ Requiere confirmación

### Al Eliminar un Usuario:
1. ❌ **NO** se puede eliminar si tiene préstamos activos
2. ⚠️ Requiere confirmación

---

## 📁 Archivos SQL Disponibles

- `sql/estructura_completa.sql` - Crea/actualiza toda la estructura
- `sql/insertar_datos_basicos.sql` - Inserta libros y categorías de ejemplo
- `sql/datos_completos.sql` - Datos completos con usuarios y préstamos (usar con precaución)

---

## 🔧 Mantenimiento

### Actualizar estados de préstamos vencidos:
```sql
UPDATE prestamos 
SET estado = 'vencido' 
WHERE estado = 'activo' 
AND fecha_devolucion < CURDATE();
```

### Ver estadísticas:
```sql
SELECT 
    COUNT(*) as total_libros,
    SUM(CASE WHEN estado='disponible' THEN 1 ELSE 0 END) as disponibles,
    SUM(CASE WHEN estado='prestado' THEN 1 ELSE 0 END) as prestados
FROM libros WHERE activo=1;
```

---

## 📝 Notas Importantes

1. **Seguridad:**
   - Todas las contraseñas están hasheadas con `password_hash()`
   - Usar prepared statements en todas las consultas
   - Validar inputs en cliente y servidor

2. **Diferencia entre tablas de usuarios:**
   - `usuarios`: Socios que piden libros prestados
   - `usuarios_sistema`: Staff que opera el sistema

3. **Estados de Préstamo:**
   - `activo`: En curso, libro no devuelto
   - `devuelto`: Finalizado correctamente
   - `vencido`: Activo pero pasó la fecha de devolución

---

**Última actualización:** 5 de noviembre de 2025
