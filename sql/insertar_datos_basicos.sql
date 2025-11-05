-- ============================================
-- DATOS BÁSICOS DE PRUEBA
-- SISTEMA DE BIBLIOTECA LGI
-- ============================================

USE libreria;

-- Insertar categorías
INSERT INTO categorias (nombre, descripcion) VALUES
('Ficción', 'Novelas y cuentos de ficción'),
('Ciencia Ficción', 'Literatura de ciencia ficción y fantasía'),
('Historia', 'Libros de historia y biografías'),
('Ciencia', 'Libros científicos y divulgativos'),
('Tecnología', 'Libros sobre tecnología'),
('Educación', 'Libros educativos'),
('Filosofía', 'Obras filosóficas'),
('Poesía', 'Colecciones de poesía'),
('Arte', 'Libros de arte'),
('Infantil', 'Literatura infantil')
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion);

-- Insertar libros (asegurándonos que no existan previamente)
INSERT IGNORE INTO libros (titulo, autor, isbn, editorial, ano_publicacion, categoria, descripcion, estado, stock) VALUES
('Cien años de soledad', 'Gabriel García Márquez', '9780307474728', 'Sudamericana', 1967, 'Ficción', 'Historia de la familia Buendía', 'disponible', 2),
('Don Quijote de la Mancha', 'Miguel de Cervantes', '9788420412146', 'Espasa', 1605, 'Ficción', 'Las aventuras de un hidalgo', 'disponible', 3),
('Rayuela', 'Julio Cortázar', '9788420471891', 'Alfaguara', 1963, 'Ficción', 'Novela experimental', 'disponible', 1),
('1984', 'George Orwell', '9780451524935', 'Signet', 1949, 'Ficción', 'Distopía totalitaria', 'disponible', 2),
('El principito', 'Antoine de Saint-Exupéry', '9780156012195', 'Harcourt', 1943, 'Infantil', 'Fábula filosófica', 'disponible', 3),
('Hamlet', 'William Shakespeare', '9780743477123', 'Simon & Schuster', 1603, 'Ficción', 'Tragedia clásica', 'disponible', 1),
('Fundación', 'Isaac Asimov', '9780553293357', 'Spectra', 1951, 'Ciencia Ficción', 'Saga del Imperio Galáctico', 'disponible', 2),
('Dune', 'Frank Herbert', '9780441172719', 'Ace', 1965, 'Ciencia Ficción', 'Épica espacial', 'disponible', 1),
('El Hobbit', 'J.R.R. Tolkien', '9780547928227', 'Del Rey', 1937, 'Ciencia Ficción', 'Aventura en la Tierra Media', 'disponible', 2),
('Sapiens', 'Yuval Noah Harari', '9780062316097', 'Harper', 2011, 'Historia', 'Historia de la humanidad', 'disponible', 2),
('El Aleph', 'Jorge Luis Borges', '9788420633718', 'Alianza', 1949, 'Ficción', 'Colección de cuentos', 'disponible', 2),
('Clean Code', 'Robert C. Martin', '9780132350884', 'Prentice Hall', 2008, 'Tecnología', 'Código limpio', 'disponible', 1);

SELECT 'Datos insertados correctamente' AS Mensaje;
SELECT COUNT(*) AS 'Total Libros' FROM libros;
SELECT COUNT(*) AS 'Total Categorías' FROM categorias;
