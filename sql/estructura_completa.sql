-- ============================================
-- SISTEMA DE GESTIÓN DE BIBLIOTECA - LGI
-- Script de Estructura Completa
-- Fecha: 5 de noviembre de 2025
-- ============================================

-- Seleccionar la base de datos
USE libreria;

-- ============================================
-- TABLA: libros
-- Almacena el catálogo de libros de la biblioteca
-- ============================================
-- Ya existe, solo verificamos y agregamos columnas si faltan

-- Verificar que tenga todas las columnas necesarias
ALTER TABLE libros
MODIFY COLUMN titulo VARCHAR(200) NOT NULL,
MODIFY COLUMN autor VARCHAR(150) NOT NULL,
MODIFY COLUMN isbn VARCHAR(20) NOT NULL UNIQUE,
MODIFY COLUMN editorial VARCHAR(100),
MODIFY COLUMN ano_publicacion YEAR,
MODIFY COLUMN categoria VARCHAR(50),
MODIFY COLUMN descripcion TEXT,
MODIFY COLUMN estado ENUM('disponible', 'prestado') NOT NULL DEFAULT 'disponible',
MODIFY COLUMN activo TINYINT(1) DEFAULT 1,
MODIFY COLUMN fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
MODIFY COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Crear índices para mejorar búsquedas
CREATE INDEX IF NOT EXISTS idx_libro_titulo ON libros(titulo);
CREATE INDEX IF NOT EXISTS idx_libro_autor ON libros(autor);
CREATE INDEX IF NOT EXISTS idx_libro_isbn ON libros(isbn);
CREATE INDEX IF NOT EXISTS idx_libro_categoria ON libros(categoria);
CREATE INDEX IF NOT EXISTS idx_libro_estado ON libros(estado);
CREATE INDEX IF NOT EXISTS idx_libro_activo ON libros(activo);

-- ============================================
-- TABLA: usuarios
-- Almacena los socios/usuarios de la biblioteca
-- (personas que piden libros prestados)
-- ============================================
-- Ya existe, solo verificamos estructura

ALTER TABLE usuarios
MODIFY COLUMN nombre_completo VARCHAR(150) NOT NULL,
MODIFY COLUMN usuario VARCHAR(100) UNIQUE,
MODIFY COLUMN email VARCHAR(100) NOT NULL UNIQUE,
MODIFY COLUMN password VARCHAR(255),
MODIFY COLUMN telefono VARCHAR(20),
MODIFY COLUMN direccion VARCHAR(200),
MODIFY COLUMN dni VARCHAR(20) NOT NULL UNIQUE,
MODIFY COLUMN fecha_registro DATE DEFAULT (CURRENT_DATE),
MODIFY COLUMN estado ENUM('activo', 'suspendido') NOT NULL DEFAULT 'activo',
MODIFY COLUMN rol ENUM('usuario', 'bibliotecario', 'admin') DEFAULT 'usuario',
MODIFY COLUMN activo TINYINT(1) DEFAULT 1,
MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
MODIFY COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Crear índices
CREATE INDEX IF NOT EXISTS idx_usuario_email ON usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuario_dni ON usuarios(dni);
CREATE INDEX IF NOT EXISTS idx_usuario_estado ON usuarios(estado);
CREATE INDEX IF NOT EXISTS idx_usuario_rol ON usuarios(rol);
CREATE INDEX IF NOT EXISTS idx_usuario_activo ON usuarios(activo);

-- ============================================
-- TABLA: prestamos
-- Registra todos los préstamos de libros
-- ============================================
-- Ya existe, verificamos estructura

ALTER TABLE prestamos
MODIFY COLUMN libro_id INT(11) NOT NULL,
MODIFY COLUMN usuario_id INT(11) NOT NULL,
MODIFY COLUMN fecha_prestamo DATE NOT NULL DEFAULT (CURRENT_DATE),
MODIFY COLUMN fecha_devolucion DATE NOT NULL,
MODIFY COLUMN fecha_dev_real DATE NULL,
MODIFY COLUMN estado ENUM('activo', 'devuelto', 'vencido') NOT NULL DEFAULT 'activo',
MODIFY COLUMN observaciones TEXT,
MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Crear índices
CREATE INDEX IF NOT EXISTS idx_prestamo_libro ON prestamos(libro_id);
CREATE INDEX IF NOT EXISTS idx_prestamo_usuario ON prestamos(usuario_id);
CREATE INDEX IF NOT EXISTS idx_prestamo_estado ON prestamos(estado);
CREATE INDEX IF NOT EXISTS idx_prestamo_fecha_devolucion ON prestamos(fecha_devolucion);
CREATE INDEX IF NOT EXISTS idx_prestamo_fecha_prestamo ON prestamos(fecha_prestamo);

-- ============================================
-- TABLA: usuarios_sistema
-- Usuarios que operan el sistema (bibliotecarios/admins)
-- Diferente de la tabla 'usuarios' (socios)
-- ============================================
-- Ya existe, verificamos estructura

ALTER TABLE usuarios_sistema
MODIFY COLUMN usuario VARCHAR(50) NOT NULL UNIQUE,
MODIFY COLUMN password VARCHAR(255) NOT NULL,
MODIFY COLUMN nombre VARCHAR(100) NOT NULL,
MODIFY COLUMN email VARCHAR(100) NOT NULL UNIQUE,
MODIFY COLUMN rol ENUM('admin', 'bibliotecario') NOT NULL DEFAULT 'bibliotecario',
MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================
-- TABLA: categorias (OPCIONAL - para funcionalidad extra)
-- ============================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear índice
CREATE INDEX IF NOT EXISTS idx_categoria_nombre ON categorias(nombre);
CREATE INDEX IF NOT EXISTS idx_categoria_activo ON categorias(activo);

-- ============================================
-- CONFIGURACIÓN
-- ============================================
-- La tabla configuracion ya existe, solo agregamos columnas si faltan

-- Agregar columnas faltantes
SET @sql = 'ALTER TABLE configuracion ADD COLUMN tipo ENUM(\'texto\', \'numero\', \'fecha\', \'booleano\') DEFAULT \'texto\' AFTER descripcion';
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insertar configuraciones por defecto del sistema
INSERT INTO configuracion (clave, valor, descripcion, tipo) VALUES
('dias_prestamo', '14', 'Días de préstamo por defecto', 'numero'),
('max_prestamos_usuario', '3', 'Máximo de préstamos simultáneos por usuario', 'numero'),
('multa_dia_atraso', '50', 'Multa en pesos por día de atraso', 'numero'),
('dias_alerta_vencimiento', '3', 'Días antes de vencer para alertar', 'numero'),
('nombre_biblioteca', 'Biblioteca LGI', 'Nombre de la biblioteca', 'texto'),
('email_biblioteca', 'biblioteca@lgi.com', 'Email de contacto', 'texto'),
('telefono_biblioteca', '(011) 1234-5678', 'Teléfono de contacto', 'texto')
ON DUPLICATE KEY UPDATE valor=VALUES(valor);

-- ============================================
-- LOGS DEL SISTEMA (ya existe)
-- ============================================
CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT(11),
    accion VARCHAR(100) NOT NULL,
    tabla VARCHAR(50),
    registro_id INT(11),
    descripcion TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_logs_usuario ON logs_sistema(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_accion ON logs_sistema(accion);
CREATE INDEX IF NOT EXISTS idx_logs_fecha ON logs_sistema(created_at);

-- ============================================
-- VERIFICAR CLAVES FORÁNEAS
-- ============================================

-- Eliminar claves foráneas existentes si existen (para recrear)
ALTER TABLE prestamos DROP FOREIGN KEY IF EXISTS fk_prestamos_libro;
ALTER TABLE prestamos DROP FOREIGN KEY IF EXISTS fk_prestamos_usuario;

-- Crear claves foráneas con las opciones correctas
ALTER TABLE prestamos
ADD CONSTRAINT fk_prestamos_libro 
    FOREIGN KEY (libro_id) REFERENCES libros(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE prestamos
ADD CONSTRAINT fk_prestamos_usuario 
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- ============================================
-- FIN DEL SCRIPT DE ESTRUCTURA
-- ============================================
