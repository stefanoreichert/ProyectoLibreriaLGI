# Sistema de Permisos por Roles

## Roles del Sistema

### 1. Usuario (rol: 'usuario')
**Permisos:**
- ✅ Ver catálogo de libros (solo lectura)
- ✅ Buscar libros
- ✅ Ver sus propios préstamos
- ✅ Ver su propio perfil
- ❌ NO puede crear/editar/eliminar libros
- ❌ NO puede crear/editar/eliminar usuarios
- ❌ NO puede gestionar préstamos de otros usuarios
- ❌ NO puede acceder a reportes
- ❌ NO puede acceder a configuración

**Accesos permitidos:**
- Dashboard (vista limitada)
- libros/index.php (solo lectura)
- libros/buscar.php (solo búsqueda)
- prestamos/index.php (solo sus préstamos)
- usuarios/detalle.php (solo su perfil)

### 2. Bibliotecario (rol: 'bibliotecario')
**Permisos:**
- ✅ Todo lo de Usuario +
- ✅ Crear/editar/eliminar libros
- ✅ Gestionar todos los préstamos (crear, devolver)
- ✅ Ver todos los usuarios (solo lectura)
- ✅ Ver reportes
- ❌ NO puede crear/editar/eliminar usuarios
- ❌ NO puede acceder a configuración del sistema

**Accesos permitidos:**
- Todo lo de Usuario +
- libros/crear.php, libros/editar.php, libros/eliminar.php
- prestamos/nuevo.php, prestamos/devolver.php
- usuarios/index.php (solo lectura)
- reportes.php

### 3. Administrador (rol: 'admin')
**Permisos:**
- ✅ Todo lo de Bibliotecario +
- ✅ Crear/editar/eliminar usuarios
- ✅ Cambiar roles de usuarios
- ✅ Acceder a configuración del sistema
- ✅ Ver logs del sistema

**Accesos permitidos:**
- TODO el sistema sin restricciones
- usuarios/crear.php, usuarios/editar.php, usuarios/eliminar.php
- configuracion.php

## Matriz de Permisos

| Acción | Usuario | Bibliotecario | Admin |
|--------|---------|---------------|-------|
| Ver libros | ✅ | ✅ | ✅ |
| Crear libros | ❌ | ✅ | ✅ |
| Editar libros | ❌ | ✅ | ✅ |
| Eliminar libros | ❌ | ✅ | ✅ |
| Ver usuarios | Solo propio | Todos (lectura) | Todos |
| Crear usuarios | ❌ | ❌ | ✅ |
| Editar usuarios | Solo propio | ❌ | ✅ |
| Eliminar usuarios | ❌ | ❌ | ✅ |
| Ver préstamos | Solo propios | Todos | Todos |
| Crear préstamos | ❌ | ✅ | ✅ |
| Devolver libros | ❌ | ✅ | ✅ |
| Ver reportes | ❌ | ✅ | ✅ |
| Configuración | ❌ | ❌ | ✅ |

## Implementación

### Archivos a proteger:

1. **libros/crear.php** - Solo bibliotecario y admin
2. **libros/editar.php** - Solo bibliotecario y admin
3. **libros/eliminar.php** - Solo bibliotecario y admin
4. **usuarios/crear.php** - Solo admin
5. **usuarios/editar.php** - Admin o el propio usuario
6. **usuarios/eliminar.php** - Solo admin
7. **prestamos/nuevo.php** - Solo bibliotecario y admin
8. **prestamos/devolver.php** - Solo bibliotecario y admin (si existe)
9. **reportes.php** - Solo bibliotecario y admin
10. **configuracion.php** - Solo admin
11. **dashboard.php** - Vistas diferenciadas por rol
12. **header.php** - Menú según permisos

### Funciones de auth.php:
- `verificarRol(['rol1', 'rol2'])` - Verificar múltiples roles
- `isAdmin()` - Verificar si es admin
- `isBibliotecario()` - Verificar si es bibliotecario o admin
- `hasPermission('rol')` - Verificar jerarquía de roles
