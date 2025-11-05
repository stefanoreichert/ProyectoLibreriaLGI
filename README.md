# Sistema de Gestión de Biblioteca - ProyectoLibreriaLGI

## 📚 Descripción

Sistema completo de gestión de biblioteca desarrollado en PHP con MySQL. Permite la administración de libros, usuarios y préstamos con una interfaz web moderna y funcional.

## ✨ Características Principales

- **Autenticación y Autorización**: Sistema de login con roles (Admin, Bibliotecario, Usuario)
- **Gestión de Libros**: CRUD completo con búsqueda y categorización
- **Gestión de Usuarios**: Registro y administración de usuarios con diferentes roles
- **Sistema de Préstamos**: Control de préstamos con fechas automáticas y validaciones
- **Dashboard Interactivo**: Estadísticas en tiempo real y accesos rápidos
- **Reportes Avanzados**: Generación de reportes con gráficos y exportación a CSV
- **Búsquedas AJAX**: Autocompletado y búsquedas en tiempo real
- **Configuración**: Panel de administración para configurar el sistema
- **Responsive Design**: Interfaz adaptativa para diferentes dispositivos

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Librerías**: Chart.js para gráficos
- **Iconos**: Font Awesome
- **Servidor**: Apache (XAMPP recomendado)

## 📋 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache 2.4+
- Extensiones PHP: PDO, PDO_MySQL, mbstring, json

## 🚀 Instalación

### 1. Clonar el Repositorio
```bash
git clone https://github.com/stefanoreichert/ProyectoLibreriaLGI.git
cd ProyectoLibreriaLGI
```

### 2. Configurar la Base de Datos
1. Crear una base de datos MySQL llamada `biblioteca_lgi`
2. Importar la estructura:
   ```bash
   mysql -u usuario -p biblioteca_lgi < sql/estructura.sql
   ```
3. Importar datos de prueba (opcional):
   ```bash
   mysql -u usuario -p biblioteca_lgi < sql/datos_prueba.sql
   ```

### 3. Configurar la Conexión
1. Copiar `config/database.php.example` a `config/database.php`
2. Editar las credenciales de la base de datos:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'biblioteca_lgi');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```

### 4. Configurar el Servidor Web
- Apuntar el DocumentRoot a la carpeta del proyecto
- Asegurar que mod_rewrite esté habilitado

## 👤 Usuarios de Prueba

Una vez importados los datos de prueba, puedes usar estas cuentas:

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| admin | password123 | Administrador |
| maria.bib | password123 | Bibliotecario |
| usuario.demo | password123 | Usuario |

## 📁 Estructura del Proyecto

```
ProyectoLibreriaLGI/
├── assets/
│   ├── css/              # Estilos CSS
│   └── js/               # Scripts JavaScript
├── config/
│   ├── database.php      # Configuración de BD
│   └── config.php        # Configuraciones generales
├── includes/
│   ├── header.php        # Cabecera común
│   ├── footer.php        # Pie de página
│   └── auth.php          # Funciones de autenticación
├── libros/               # Módulo de gestión de libros
├── usuarios/             # Módulo de gestión de usuarios
├── prestamos/            # Módulo de gestión de préstamos
├── sql/                  # Scripts de base de datos
├── dashboard.php         # Panel principal
├── login.php             # Página de login
├── reportes.php          # Sistema de reportes
└── configuracion.php     # Panel de configuración
```

## 🔧 Configuración

El sistema incluye un panel de configuración accesible solo para administradores donde se pueden ajustar:

- Información de la biblioteca
- Parámetros de préstamos (días, límites, multas)
- Configuraciones del sistema (sesiones, notificaciones)

## 📊 Funcionalidades

### Gestión de Libros
- Crear, editar, eliminar y buscar libros
- Control de stock y disponibilidad
- Categorización y filtros
- Validación de ISBN

### Gestión de Usuarios
- Registro y administración de usuarios
- Diferentes roles y permisos
- Historial de préstamos
- Validaciones de email y datos únicos

### Sistema de Préstamos
- Crear nuevos préstamos con validaciones
- Control de fechas y plazos
- Renovaciones automáticas
- Multas por retraso
- Búsqueda con autocompletado

### Reportes
- Estadísticas generales del sistema
- Libros más prestados
- Usuarios más activos
- Reportes detallados por período
- Exportación a CSV
- Gráficos interactivos

## 🔒 Seguridad

- Validación y sanitización de datos
- Protección contra inyección SQL con PDO
- Control de sesiones con timeout
- Verificación de roles y permisos
- Logging de actividades del sistema

## 🤝 Contribuir

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

**Stefano Reichert**
- GitHub: [@stefanoreichert](https://github.com/stefanoreichert)

## 🙏 Agradecimientos

- Font Awesome por los iconos
- Chart.js por los gráficos
- Comunidad PHP por la documentación

---

⭐ ¡Si te gusta este proyecto, dale una estrella en GitHub!