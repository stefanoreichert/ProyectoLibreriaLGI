-- Script para agregar tablas necesarias a la base de datos 'libreria' existente
USE libreria;

-- Tabla de usuarios (si no existe)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'bibliotecario', 'admin') DEFAULT 'usuario',
    telefono VARCHAR(20),
    documento VARCHAR(50),
    direccion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_actividad TIMESTAMP NULL,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_usuario (usuario),
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
);

-- Tabla de libros (si no existe)
CREATE TABLE IF NOT EXISTS libros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(500) NOT NULL,
    subtitulo VARCHAR(500),
    autor VARCHAR(300) NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    categoria VARCHAR(100) NOT NULL,
    editorial VARCHAR(200),
    ano_publicacion YEAR,
    paginas INT,
    descripcion TEXT,
    stock INT DEFAULT 1,
    ubicacion VARCHAR(200),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_titulo (titulo),
    INDEX idx_autor (autor),
    INDEX idx_isbn (isbn),
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo),
    FULLTEXT idx_busqueda (titulo, autor, descripcion)
);

-- Tabla de prestamos (si no existe)
CREATE TABLE IF NOT EXISTS prestamos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    libro_id INT NOT NULL,
    fecha_prestamo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion_prevista DATE NOT NULL,
    fecha_devolucion TIMESTAMP NULL,
    renovaciones INT DEFAULT 0,
    multa DECIMAL(10,2) DEFAULT 0.00,
    observaciones TEXT,
    estado ENUM('activo', 'devuelto', 'vencido') DEFAULT 'activo',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_libro (libro_id),
    INDEX idx_fecha_prestamo (fecha_prestamo),
    INDEX idx_fecha_devolucion_prevista (fecha_devolucion_prevista),
    INDEX idx_estado (estado)
);

-- Tabla de configuracion (si no existe)
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descripcion TEXT,
    actualizado_por INT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_clave (clave)
);

-- Tabla de logs del sistema (si no existe)
CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_modulo (modulo),
    INDEX idx_accion (accion),
    INDEX idx_fecha (fecha)
);

-- Insertar usuario administrador si no existe
INSERT IGNORE INTO usuarios (nombre_completo, usuario, email, password, rol, telefono, documento, direccion) VALUES
('Administrator', 'admin', 'admin@biblioteca.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '123-456-7890', 'ADM001', 'Oficina Principal');

-- Insertar configuraciones básicas si no existen
INSERT IGNORE INTO configuracion (clave, valor, descripcion) VALUES
('nombre_biblioteca', 'Biblioteca Central', 'Nombre de la biblioteca'),
('max_libros_usuario', '3', 'Máximo de libros por usuario'),
('dias_prestamo_defecto', '15', 'Días de préstamo por defecto'),
('session_timeout', '30', 'Tiempo de sesión en minutos');