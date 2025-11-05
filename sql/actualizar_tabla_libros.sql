-- Agregar columnas faltantes a la tabla libros
USE libreria;

-- Agregar columna activo
ALTER TABLE libros ADD COLUMN activo BOOLEAN DEFAULT TRUE AFTER estado;

-- Agregar columna stock 
ALTER TABLE libros ADD COLUMN stock INT DEFAULT 1 AFTER descripcion;

-- Agregar columna ubicacion
ALTER TABLE libros ADD COLUMN ubicacion VARCHAR(200) AFTER stock;

-- Agregar columna subtitulo
ALTER TABLE libros ADD COLUMN subtitulo VARCHAR(500) AFTER titulo;

-- Agregar columna paginas
ALTER TABLE libros ADD COLUMN paginas INT AFTER anio;

-- Agregar columna fecha_registro (renombrar created_at)
ALTER TABLE libros CHANGE created_at fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Renombrar columna anio a ano_publicacion 
ALTER TABLE libros CHANGE anio ano_publicacion YEAR;

-- Agregar índices
ALTER TABLE libros ADD INDEX idx_activo (activo);
ALTER TABLE libros ADD INDEX idx_categoria (categoria);

-- Actualizar todos los libros existentes como activos
UPDATE libros SET activo = TRUE WHERE activo IS NULL;

-- Mostrar resultado
SELECT 'Tabla libros actualizada correctamente' as resultado;
DESCRIBE libros;