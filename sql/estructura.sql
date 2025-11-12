-- Base de datos para Sistema de Librería LGI
-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS libreria_lgi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE libreria_lgi;

-- Tabla de usuarios
CREATE TABLE usuarios (
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

-- Tabla de libros
CREATE TABLE libros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(500) NOT NULL,
    subtitulo VARCHAR(500),
    autor VARCHAR(300) NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    categoria VARCHAR(100) NOT NULL,
    editorial VARCHAR(200),
    año_publicacion YEAR,
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

-- Tabla de préstamos
CREATE TABLE prestamos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    libro_id INT NOT NULL,
    fecha_prestamo DATE NOT NULL,
    fecha_limite DATE NOT NULL,
    fecha_devolucion DATE NULL,
    observaciones TEXT,
    multa DECIMAL(10,2) DEFAULT 0.00,
    creado_por INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE RESTRICT,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_usuario (usuario_id),
    INDEX idx_libro (libro_id),
    INDEX idx_fecha_prestamo (fecha_prestamo),
    INDEX idx_fecha_limite (fecha_limite),
    INDEX idx_fecha_devolucion (fecha_devolucion),
    INDEX idx_activos (fecha_devolucion) -- Para préstamos activos (fecha_devolucion IS NULL)
);

-- Tabla de configuración del sistema
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descripcion TEXT,
    tipo ENUM('texto', 'numero', 'booleano', 'fecha') DEFAULT 'texto',
    categoria VARCHAR(100) DEFAULT 'general',
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de logs del sistema
CREATE TABLE logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50),
    registro_id INT,
    datos_anteriores JSON,
    datos_nuevos JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (fecha),
    INDEX idx_tabla (tabla_afectada)
);

-- Tabla de sesiones (opcional, para manejo avanzado de sesiones)
CREATE TABLE sesiones (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    datos TEXT,
    ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_actividad (ultima_actividad)
);

-- Triggers para logs automáticos

-- Trigger para logs en usuarios
DELIMITER $$
CREATE TRIGGER tr_usuarios_insert AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (usuario_id, accion, tabla_afectada, registro_id, datos_nuevos)
    VALUES (NEW.id, 'INSERT', 'usuarios', NEW.id, JSON_OBJECT(
        'nombre', NEW.nombre,
        'usuario', NEW.usuario,
        'email', NEW.email,
        'rol', NEW.rol
    ));
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER tr_usuarios_update AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (usuario_id, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos)
    VALUES (NEW.id, 'UPDATE', 'usuarios', NEW.id, 
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'usuario', OLD.usuario,
            'email', OLD.email,
            'rol', OLD.rol,
            'activo', OLD.activo
        ),
        JSON_OBJECT(
            'nombre', NEW.nombre,
            'usuario', NEW.usuario,
            'email', NEW.email,
            'rol', NEW.rol,
            'activo', NEW.activo
        )
    );
END$$
DELIMITER ;

-- Trigger para logs en libros
DELIMITER $$
CREATE TRIGGER tr_libros_insert AFTER INSERT ON libros
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (accion, tabla_afectada, registro_id, datos_nuevos)
    VALUES ('INSERT', 'libros', NEW.id, JSON_OBJECT(
        'titulo', NEW.titulo,
        'autor', NEW.autor,
        'isbn', NEW.isbn,
        'stock', NEW.stock
    ));
END$$
DELIMITER ;

-- Trigger para logs en préstamos
DELIMITER $$
CREATE TRIGGER tr_prestamos_insert AFTER INSERT ON prestamos
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (usuario_id, accion, tabla_afectada, registro_id, datos_nuevos)
    VALUES (NEW.creado_por, 'INSERT', 'prestamos', NEW.id, JSON_OBJECT(
        'usuario_id', NEW.usuario_id,
        'libro_id', NEW.libro_id,
        'fecha_prestamo', NEW.fecha_prestamo,
        'fecha_limite', NEW.fecha_limite
    ));
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER tr_prestamos_update AFTER UPDATE ON prestamos
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (usuario_id, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos)
    VALUES (NEW.creado_por, 'UPDATE', 'prestamos', NEW.id,
        JSON_OBJECT(
            'fecha_devolucion', OLD.fecha_devolucion,
            'multa', OLD.multa
        ),
        JSON_OBJECT(
            'fecha_devolucion', NEW.fecha_devolucion,
            'multa', NEW.multa
        )
    );
END$$
DELIMITER ;

-- Vistas útiles

-- Vista de préstamos activos con información completa
CREATE VIEW v_prestamos_activos AS
SELECT 
    p.id,
    p.fecha_prestamo,
    p.fecha_limite,
    DATEDIFF(CURDATE(), p.fecha_limite) as dias_vencido,
    u.id as usuario_id,
    u.nombre as usuario_nombre,
    u.email as usuario_email,
    u.telefono as usuario_telefono,
    l.id as libro_id,
    l.titulo as libro_titulo,
    l.autor as libro_autor,
    l.isbn as libro_isbn,
    p.observaciones,
    CASE 
        WHEN p.fecha_limite < CURDATE() THEN 'vencido'
        WHEN DATEDIFF(p.fecha_limite, CURDATE()) <= 3 THEN 'por_vencer'
        ELSE 'activo'
    END as estado
FROM prestamos p
JOIN usuarios u ON p.usuario_id = u.id
JOIN libros l ON p.libro_id = l.id
WHERE p.fecha_devolucion IS NULL
  AND u.activo = 1
  AND l.activo = 1;

-- Vista de estadísticas generales
CREATE VIEW v_estadisticas_generales AS
SELECT 
    (SELECT COUNT(*) FROM usuarios WHERE activo = 1) as total_usuarios,
    (SELECT COUNT(*) FROM libros WHERE activo = 1) as total_libros,
    (SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL) as prestamos_activos,
    (SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL AND fecha_limite < CURDATE()) as prestamos_vencidos,
    (SELECT SUM(stock) FROM libros WHERE activo = 1) as total_ejemplares,
    (SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL) as ejemplares_prestados;

-- Vista de libros más prestados
CREATE VIEW v_libros_mas_prestados AS
SELECT 
    l.id,
    l.titulo,
    l.autor,
    l.isbn,
    COUNT(p.id) as total_prestamos,
    COUNT(CASE WHEN p.fecha_devolucion IS NULL THEN 1 END) as prestamos_activos
FROM libros l
LEFT JOIN prestamos p ON l.id = p.libro_id
WHERE l.activo = 1
GROUP BY l.id, l.titulo, l.autor, l.isbn
ORDER BY total_prestamos DESC;

-- Vista de usuarios más activos
CREATE VIEW v_usuarios_mas_activos AS
SELECT 
    u.id,
    u.nombre,
    u.email,
    COUNT(p.id) as total_prestamos,
    COUNT(CASE WHEN p.fecha_devolucion IS NULL THEN 1 END) as prestamos_activos,
    MAX(p.fecha_prestamo) as ultimo_prestamo
FROM usuarios u
LEFT JOIN prestamos p ON u.id = p.usuario_id
WHERE u.activo = 1
GROUP BY u.id, u.nombre, u.email
ORDER BY total_prestamos DESC;

-- Tabla de contactos
CREATE TABLE contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'en_proceso', 'resuelto') DEFAULT 'pendiente',
    respuesta TEXT,
    fecha_respuesta TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_envio (fecha_envio)
);