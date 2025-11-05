-- Agregar campos necesarios para autenticación a la tabla usuarios existente
USE libreria;

-- Agregar campos faltantes para el sistema de autenticación
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS usuario VARCHAR(100) UNIQUE AFTER nombre_completo,
ADD COLUMN IF NOT EXISTS password VARCHAR(255) AFTER usuario,
ADD COLUMN IF NOT EXISTS rol ENUM('usuario', 'bibliotecario', 'admin') DEFAULT 'usuario' AFTER password,
ADD COLUMN IF NOT EXISTS activo BOOLEAN DEFAULT TRUE AFTER estado;

-- Crear índices para los nuevos campos
ALTER TABLE usuarios 
ADD INDEX IF NOT EXISTS idx_usuario (usuario),
ADD INDEX IF NOT EXISTS idx_rol (rol),
ADD INDEX IF NOT EXISTS idx_activo (activo);

-- Insertar usuario administrador de prueba (solo si no existe ya un admin)
INSERT IGNORE INTO usuarios (nombre_completo, usuario, email, password, rol, telefono, dni, direccion, activo) VALUES
('Administrator', 'admin', 'admin@biblioteca.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '123-456-7890', 'ADM001', 'Oficina Principal', TRUE);

-- Agregar algunos usuarios de prueba adicionales
INSERT IGNORE INTO usuarios (nombre_completo, usuario, email, password, rol, telefono, dni, direccion, activo) VALUES
('María Bibliotecaria', 'maria.bib', 'maria@biblioteca.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bibliotecario', '123-456-7891', 'BIB001', 'Departamento de Préstamos', TRUE),
('Usuario Demo', 'usuario.demo', 'usuario@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', '123-456-7892', 'USR001', 'Casa del Usuario', TRUE);

-- Verificar que la tabla configuracion tenga las configuraciones básicas
INSERT IGNORE INTO configuracion (clave, valor) VALUES
('nombre_biblioteca', 'Biblioteca Central'),
('max_libros_usuario', '3'),
('dias_prestamo_defecto', '15'),
('session_timeout', '30');

SELECT 'Setup completado correctamente' as resultado;