# 🔐 Matriz de Permisos por Roles

## Roles del Sistema
- **Usuario**: Miembros regulares que pueden solicitar préstamos
- **Bibliotecario**: Personal que gestiona libros y préstamos (SIN acceso a usuarios)
- **Admin**: Acceso completo al sistema

---

## 📚 **LIBROS**

| Funcionalidad | Usuario | Bibliotecario | Admin |
|--------------|---------|---------------|-------|
| Ver catálogo | ✅ | ✅ | ✅ |
| Ver detalles | ✅ | ✅ | ✅ |
| Crear libro | ❌ | ✅ | ✅ |
| Editar libro | ❌ | ✅ | ✅ |
| Eliminar libro | ❌ | ✅ | ✅ |
| Solicitar préstamo | ✅ | ✅ | ✅ |

---

## 👥 **USUARIOS**

| Funcionalidad | Usuario | Bibliotecario | Admin |
|--------------|---------|---------------|-------|
| Ver listado | ❌ | ✅ (solo lectura) | ✅ |
| Ver detalle propio | ✅ | ✅ | ✅ |
| Ver detalle otros | ❌ | ✅ (solo lectura) | ✅ |
| Crear usuario | ❌ | ❌ | ✅ |
| Editar usuario | ❌ | ❌ | ✅ |
| Eliminar usuario | ❌ | ❌ | ✅ |
| Cambiar rol | ❌ | ❌ | ✅ |

---

## 📖 **PRÉSTAMOS**

| Funcionalidad | Usuario | Bibliotecario | Admin |
|--------------|---------|---------------|-------|
| Ver mis préstamos | ✅ | ✅ | ✅ |
| Ver todos los préstamos | ❌ | ✅ | ✅ |
| Solicitar préstamo | ✅ | ✅ | ✅ |
| Crear préstamo (otro usuario) | ❌ | ✅ | ✅ |
| Devolver libro | ❌ | ✅ | ✅ |
| Ver historial completo | ❌ | ✅ | ✅ |

---

## 📊 **REPORTES Y ESTADÍSTICAS**

| Funcionalidad | Usuario | Bibliotecario | Admin |
|--------------|---------|---------------|-------|
| Ver estadísticas propias | ✅ | ✅ | ✅ |
| Ver reportes del sistema | ❌ | ✅ | ✅ |
| Dashboard completo | ❌ | ✅ | ✅ |

---

## ⚙️ **CONFIGURACIÓN Y SISTEMA**

| Funcionalidad | Usuario | Bibliotecario | Admin |
|--------------|---------|---------------|-------|
| Ver configuración | ❌ | ❌ | ✅ |
| Modificar configuración | ❌ | ❌ | ✅ |
| Ver logs del sistema | ❌ | ❌ | ✅ |
| Gestionar parámetros | ❌ | ❌ | ✅ |

---

## 🔑 **Resumen de Cambios Recientes**

### Restricciones aplicadas al rol **Bibliotecario**:

✅ **PUEDE hacer:**
- Gestionar el catálogo de libros (crear, editar, eliminar)
- Ver el listado completo de usuarios (solo lectura)
- Ver detalles de cualquier usuario (solo lectura)
- Gestionar todos los préstamos (crear, devolver)
- Ver reportes y estadísticas del sistema
- Acceder al dashboard completo

❌ **NO PUEDE hacer:**
- Crear nuevos usuarios
- Editar información de usuarios existentes
- Eliminar usuarios
- Cambiar roles de usuarios
- Acceder a la configuración del sistema
- Modificar parámetros globales

---

## 📋 **Archivos Modificados**

1. **usuarios/index.php**
   - Botón "Nuevo Usuario" solo visible para admins
   - Botón "Editar" solo visible para admins
   - Bibliotecarios solo ven el botón "Ver detalle"

2. **usuarios/crear.php**
   - Acceso restringido solo a admins
   - Redirige a dashboard si no es admin

3. **usuarios/editar.php**
   - Acceso restringido solo a admins
   - Redirige a dashboard si no es admin

4. **usuarios/detalle.php**
   - Botón "Editar" solo visible para admins
   - Bibliotecarios pueden ver toda la información pero no modificar

---

## 🎯 **Casos de Uso**

### Bibliotecario Juan:
- ✅ Puede ver la ficha de un usuario para conocer su historial de préstamos
- ✅ Puede crear un nuevo préstamo para ese usuario
- ❌ NO puede cambiar el email o teléfono del usuario
- ❌ NO puede crear una cuenta nueva de usuario

### Admin María:
- ✅ Acceso completo a todo el sistema
- ✅ Puede crear/editar/eliminar usuarios
- ✅ Puede modificar roles y permisos
- ✅ Puede acceder a la configuración del sistema

### Usuario Carlos:
- ✅ Puede ver y solicitar préstamos de libros
- ✅ Puede ver su propio perfil e historial
- ❌ NO puede ver información de otros usuarios
- ❌ NO puede acceder a funciones administrativas
