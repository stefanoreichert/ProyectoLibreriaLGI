-- ============================================
-- DATOS DE PRUEBA COMPLETOS
-- SISTEMA DE BIBLIOTECA LGI
-- Fecha: 5 de noviembre de 2025
-- ============================================

USE libreria;

-- ============================================
-- 1. USUARIOS DEL SISTEMA (Login)
-- Contraseñas en texto plano (SOLO PARA DESARROLLO)
-- admin123, biblio123, operator123
-- ============================================

INSERT INTO usuarios_sistema (usuario, password, nombre, email, rol) VALUES
('admin', 'admin123', 'Administrador Sistema', 'admin@biblioteca.com', 'admin'),
('biblio', 'biblio123', 'Juan Bibliotecario', 'biblio@biblioteca.com', 'bibliotecario'),
('operator', 'operator123', 'María Operadora', 'maria@biblioteca.com', 'bibliotecario')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), email=VALUES(email);

-- ============================================
-- 2. CATEGORÍAS DE LIBROS
-- ============================================

INSERT INTO categorias (nombre, descripcion) VALUES
('Ficción', 'Novelas y cuentos de ficción'),
('Ciencia Ficción', 'Literatura de ciencia ficción y fantasía'),
('Historia', 'Libros de historia y biografías'),
('Ciencia', 'Libros científicos y divulgativos'),
('Tecnología', 'Libros sobre tecnología e informática'),
('Educación', 'Libros educativos y académicos'),
('Filosofía', 'Obras filosóficas y de pensamiento'),
('Poesía', 'Colecciones de poesía'),
('Arte', 'Libros de arte y cultura'),
('Infantil', 'Literatura infantil y juvenil')
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion);

-- ============================================
-- 3. LIBROS DEL CATÁLOGO (30 libros variados)
-- ============================================

INSERT INTO libros (titulo, autor, isbn, editorial, ano_publicacion, categoria, descripcion, estado, stock) VALUES
('Cien años de soledad', 'Gabriel García Márquez', '978-0307474728', 'Editorial Sudamericana', 1967, 'Ficción', 'Historia de la familia Buendía a lo largo de siete generaciones en el pueblo ficticio de Macondo.', 'disponible', 2),
('Don Quijote de la Mancha', 'Miguel de Cervantes', '978-8420412146', 'Espasa', 1605, 'Ficción', 'Las aventuras de un hidalgo que pierde la cordura y se lanza como caballero andante.', 'disponible', 3),
('Rayuela', 'Julio Cortázar', '978-8420471891', 'Alfaguara', 1963, 'Ficción', 'Novela experimental que puede leerse de múltiples formas.', 'disponible', 1),
('El amor en los tiempos del cólera', 'Gabriel García Márquez', '978-0307389732', 'Diana', 1985, 'Ficción', 'Historia de amor que transcurre más de medio siglo.', 'prestado', 1),
('Pedro Páramo', 'Juan Rulfo', '978-8445074541', 'RM', 1955, 'Ficción', 'Novela sobre un hombre que busca a su padre en un pueblo fantasma.', 'disponible', 2),
('1984', 'George Orwell', '978-0451524935', 'Signet Classic', 1949, 'Ficción', 'Distopía sobre un régimen totalitario que controla todos los aspectos de la vida.', 'disponible', 2),
('Orgullo y prejuicio', 'Jane Austen', '978-0141439518', 'Penguin Classics', 1813, 'Ficción', 'Novela romántica sobre Elizabeth Bennet y el Sr. Darcy.', 'disponible', 2),
('El principito', 'Antoine de Saint-Exupéry', '978-0156012195', 'Harcourt', 1943, 'Infantil', 'Fábula filosófica sobre un pequeño príncipe que viaja entre planetas.', 'prestado', 3),
('Hamlet', 'William Shakespeare', '978-0743477123', 'Simon & Schuster', 1603, 'Ficción', 'Tragedia sobre el príncipe de Dinamarca y su búsqueda de venganza.', 'disponible', 1),
('Ulises', 'James Joyce', '978-0141182803', 'Penguin', 1922, 'Ficción', 'Novela experimental que narra un día en la vida de Leopold Bloom.', 'disponible', 1),
('Fundación', 'Isaac Asimov', '978-0553293357', 'Spectra', 1951, 'Ciencia Ficción', 'Primera novela de la saga sobre el Imperio Galáctico.', 'disponible', 2),
('Dune', 'Frank Herbert', '978-0441172719', 'Ace', 1965, 'Ciencia Ficción', 'Épica espacial sobre el planeta desértico Arrakis.', 'disponible', 1),
('El Hobbit', 'J.R.R. Tolkien', '978-0547928227', 'Del Rey', 1937, 'Ciencia Ficción', 'Aventura de Bilbo Bolsón en la Tierra Media.', 'prestado', 2),
('Fahrenheit 451', 'Ray Bradbury', '978-1451673319', 'Simon & Schuster', 1953, 'Ciencia Ficción', 'Distopía donde los libros están prohibidos y son quemados.', 'disponible', 1),
('Neuromante', 'William Gibson', '978-0441569595', 'Ace', 1984, 'Ciencia Ficción', 'Novela cyberpunk sobre un hacker en un futuro distópico.', 'disponible', 1),
('Sapiens: De animales a dioses', 'Yuval Noah Harari', '978-0062316097', 'Harper', 2011, 'Historia', 'Historia de la humanidad desde la Edad de Piedra hasta el siglo XXI.', 'disponible', 2),
('El Diario de Ana Frank', 'Ana Frank', '978-0553577129', 'Bantam', 1947, 'Historia', 'Diario de una niña judía durante la ocupación nazi de Holanda.', 'disponible', 2),
('Breve historia del tiempo', 'Stephen Hawking', '978-0553380163', 'Bantam', 1988, 'Ciencia', 'Divulgación sobre el universo, el espacio y el tiempo.', 'disponible', 1),
('El mundo de Sofía', 'Jostein Gaarder', '978-8478886456', 'Siruela', 1991, 'Filosofía', 'Novela que introduce la historia de la filosofía.', 'disponible', 1),
('Meditaciones', 'Marco Aurelio', '978-8420674438', 'Gredos', 180, 'Filosofía', 'Reflexiones del emperador romano sobre la vida y la virtud.', 'disponible', 1),
('Veinte poemas de amor', 'Pablo Neruda', '978-8437604039', 'Cátedra', 1924, 'Poesía', 'Colección de poemas de amor del poeta chileno.', 'disponible', 2),
('Antología poética', 'Federico García Lorca', '978-8437604886', 'Cátedra', 1954, 'Poesía', 'Selección de la obra poética de Lorca.', 'disponible', 1),
('Los detectives salvajes', 'Roberto Bolaño', '978-8433920645', 'Anagrama', 1998, 'Ficción', 'Novela sobre dos jóvenes poetas.', 'disponible', 1),
('El túnel', 'Ernesto Sabato', '978-8432217104', 'Seix Barral', 1948, 'Ficción', 'Novela psicológica sobre un pintor obsesionado.', 'disponible', 1),
('Crónica de una muerte anunciada', 'Gabriel García Márquez', '978-0307387387', 'Diana', 1981, 'Ficción', 'Reconstrucción de un asesinato anunciado.', 'prestado', 1),
('Cómo ganar amigos', 'Dale Carnegie', '978-0671027032', 'Pocket Books', 1936, 'Educación', 'Libro sobre relaciones interpersonales.', 'disponible', 1),
('El arte de la guerra', 'Sun Tzu', '978-1599869773', 'Filiquarian', 500, 'Filosofía', 'Tratado militar sobre estrategia.', 'disponible', 2),
('El hombre que amaba a los perros', 'Leonardo Padura', '978-8483838082', 'Tusquets', 2009, 'Ficción', 'Novela histórica sobre Trotsky.', 'disponible', 1),
('Clean Code', 'Robert C. Martin', '978-0132350884', 'Prentice Hall', 2008, 'Tecnología', 'Guía para escribir código limpio.', 'disponible', 1),
('El Aleph', 'Jorge Luis Borges', '978-8420633718', 'Alianza', 1949, 'Ficción', 'Colección de cuentos del maestro argentino.', 'disponible', 2)
ON DUPLICATE KEY UPDATE stock=stock;

-- ============================================
-- 4. USUARIOS/SOCIOS (15 usuarios)
-- Contraseña en texto plano (SOLO PARA DESARROLLO): user123
-- ============================================

INSERT INTO usuarios (nombre_completo, usuario, email, password, telefono, dni, direccion, estado, rol) VALUES
('Juan Pérez García', 'jperez', 'juan.perez@email.com', 'user123', '11-2345-6789', '12345678', 'Av. Corrientes 1234', 'activo', 'usuario'),
('María González López', 'mgonzalez', 'maria.gonzalez@email.com', 'user123', '11-3456-7890', '23456789', 'Av. Santa Fe 2345', 'activo', 'usuario'),
('Carlos Rodríguez', 'crodriguez', 'carlos.rodriguez@email.com', 'user123', '11-4567-8901', '34567890', 'Av. Rivadavia 3456', 'activo', 'usuario'),
('Ana Martínez', 'amartinez', 'ana.martinez@email.com', 'user123', '11-5678-9012', '45678901', 'Av. Belgrano 4567', 'activo', 'usuario'),
('Luis Fernández', 'lfernandez', 'luis.fernandez@email.com', 'user123', '11-6789-0123', '56789012', 'Av. Callao 5678', 'activo', 'usuario'),
('Laura Gómez', 'lgomez', 'laura.gomez@email.com', 'user123', '11-7890-1234', '67890123', 'Av. Córdoba 6789', 'activo', 'usuario'),
('Diego Silva', 'dsilva', 'diego.silva@email.com', 'user123', '11-8901-2345', '78901234', 'Av. Pueyrredón 7890', 'activo', 'usuario'),
('Sofía Ramírez', 'sramirez', 'sofia.ramirez@email.com', 'user123', '11-9012-3456', '89012345', 'Av. Scalabrini 8901', 'activo', 'usuario'),
('Pablo Torres', 'ptorres', 'pablo.torres@email.com', 'user123', '11-0123-4567', '90123456', 'Av. Las Heras 9012', 'activo', 'usuario'),
('Carolina Díaz', 'cdiaz', 'carolina.diaz@email.com', 'user123', '11-1234-5678', '01234567', 'Av. Cabildo 1234', 'activo', 'usuario'),
('Roberto Morales', 'rmorales', 'roberto.morales@email.com', 'user123', '11-2345-6780', '11234568', 'Av. Acoyte 2345', 'activo', 'usuario'),
('Valentina Castro', 'vcastro', 'valentina.castro@email.com', 'user123', '11-3456-7891', '21234569', 'Av. Medrano 3456', 'suspendido', 'usuario'),
('Martín Ortiz', 'mortiz', 'martin.ortiz@email.com', 'user123', '11-4567-8902', '31234570', 'Av. Warnes 4567', 'activo', 'usuario'),
('Camila Ramos', 'cramos', 'camila.ramos@email.com', 'user123', '11-5678-9013', '41234571', 'Av. Triunvirato 5678', 'activo', 'usuario'),
('Sebastián Flores', 'sflores', 'sebastian.flores@email.com', 'user123', '11-6789-0124', '51234572', 'Av. Forest 6789', 'activo', 'usuario')
ON DUPLICATE KEY UPDATE nombre_completo=VALUES(nombre_completo);

-- ============================================
-- 5. PRÉSTAMOS
-- ============================================

-- Préstamos ACTIVOS
INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, fecha_devolucion, estado) VALUES
(4, 1, DATE_SUB(CURDATE(), INTERVAL 10 DAYS), DATE_ADD(CURDATE(), INTERVAL 4 DAYS), 'activo'),
(8, 1, DATE_SUB(CURDATE(), INTERVAL 8 DAYS), DATE_ADD(CURDATE(), INTERVAL 6 DAYS), 'activo'),
(13, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAYS), DATE_ADD(CURDATE(), INTERVAL 9 DAYS), 'activo'),
(25, 3, DATE_SUB(CURDATE(), INTERVAL 12 DAYS), DATE_ADD(CURDATE(), INTERVAL 2 DAYS), 'activo');

-- Préstamos VENCIDOS
INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, fecha_devolucion, estado) VALUES
(30, 12, DATE_SUB(CURDATE(), INTERVAL 20 DAYS), DATE_SUB(CURDATE(), INTERVAL 6 DAYS), 'vencido'),
(28, 12, DATE_SUB(CURDATE(), INTERVAL 18 DAYS), DATE_SUB(CURDATE(), INTERVAL 4 DAYS), 'vencido');

-- Préstamos DEVUELTOS
INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, fecha_devolucion, fecha_dev_real, estado) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 45 DAYS), DATE_SUB(CURDATE(), INTERVAL 31 DAYS), DATE_SUB(CURDATE(), INTERVAL 32 DAYS), 'devuelto'),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 40 DAYS), DATE_SUB(CURDATE(), INTERVAL 26 DAYS), DATE_SUB(CURDATE(), INTERVAL 26 DAYS), 'devuelto'),
(3, 3, DATE_SUB(CURDATE(), INTERVAL 35 DAYS), DATE_SUB(CURDATE(), INTERVAL 21 DAYS), DATE_SUB(CURDATE(), INTERVAL 20 DAYS), 'devuelto'),
(5, 4, DATE_SUB(CURDATE(), INTERVAL 50 DAYS), DATE_SUB(CURDATE(), INTERVAL 36 DAYS), DATE_SUB(CURDATE(), INTERVAL 35 DAYS), 'devuelto'),
(11, 9, DATE_SUB(CURDATE(), INTERVAL 38 DAYS), DATE_SUB(CURDATE(), INTERVAL 24 DAYS), DATE_SUB(CURDATE(), INTERVAL 22 DAYS), 'devuelto');

-- Actualizar estados de libros
UPDATE libros SET estado = 'prestado' WHERE id IN (
    SELECT DISTINCT libro_id FROM prestamos WHERE estado IN ('activo', 'vencido')
);

-- Resumen
SELECT 'DATOS INSERTADOS CORRECTAMENTE' as '';
SELECT COUNT(*) as 'Total Libros' FROM libros;
SELECT COUNT(*) as 'Usuarios Activos' FROM usuarios WHERE estado = 'activo';
SELECT COUNT(*) as 'Préstamos Activos' FROM prestamos WHERE estado = 'activo';
SELECT COUNT(*) as 'Préstamos Vencidos' FROM prestamos WHERE estado = 'vencido';
