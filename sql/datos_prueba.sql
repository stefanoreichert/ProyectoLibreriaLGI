-- Datos de prueba para el Sistema de Librería LGI
USE libreria_lgi;

-- Insertar configuración básica
INSERT INTO configuracion (clave, valor, descripcion, tipo, categoria) VALUES
('nombre_sistema', 'Sistema de Librería LGI', 'Nombre del sistema', 'texto', 'general'),
('version_sistema', '1.0.0', 'Versión del sistema', 'texto', 'general'),
('dias_prestamo_default', '15', 'Días por defecto para préstamos', 'numero', 'prestamos'),
('max_libros_usuario', '3', 'Máximo de libros por usuario', 'numero', 'prestamos'),
('multa_por_dia', '1.00', 'Multa por día de retraso', 'numero', 'prestamos'),
('email_sistema', 'sistema@libreria-lgi.com', 'Email del sistema', 'texto', 'general'),
('telefono_sistema', '+1 555-0123', 'Teléfono del sistema', 'texto', 'general'),
('direccion_sistema', 'Av. Principal 123, Ciudad', 'Dirección del sistema', 'texto', 'general');

-- Insertar usuarios de prueba
-- Contraseña para todos: password123 (hash)
INSERT INTO usuarios (nombre, usuario, email, password, rol, telefono, documento, direccion) VALUES
('Administrador Sistema', 'admin', 'admin@libreria-lgi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+1 555-0001', '12345678', 'Av. Principal 123'),
('María Bibliotecaria', 'maria.bib', 'maria@libreria-lgi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bibliotecario', '+1 555-0002', '87654321', 'Calle Secundaria 456'),
('Juan Pérez', 'juan.perez', 'juan.perez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', '+1 555-0101', '11111111', 'Calle Norte 789'),
('Ana García', 'ana.garcia', 'ana.garcia@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', '+1 555-0102', '22222222', 'Calle Sur 321'),
('Carlos López', 'carlos.lopez', 'carlos.lopez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', '+1 555-0103', '33333333', 'Calle Este 654'),
('Laura Martínez', 'laura.martinez', 'laura.martinez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', '+1 555-0104', '44444444', 'Calle Oeste 987'),
('Pedro Rodríguez', 'pedro.rodriguez', 'pedro.rodriguez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', '+1 555-0105', '55555555', 'Av. Central 159'),
('Sofía Hernández', 'sofia.hernandez', 'sofia.hernandez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', '+1 555-0106', '66666666', 'Calle Mayor 753'),
('Diego Fernández', 'diego.fernandez', 'diego.fernandez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', '+1 555-0107', '77777777', 'Av. Libertad 852'),
('Carmen Ruiz', 'carmen.ruiz', 'carmen.ruiz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', '+1 555-0108', '88888888', 'Calle Paz 951');

-- Insertar libros de prueba
INSERT INTO libros (titulo, subtitulo, autor, isbn, categoria, editorial, año_publicacion, paginas, descripcion, stock, ubicacion) VALUES
('Cien años de soledad', NULL, 'Gabriel García Márquez', '978-84-376-0494-7', 'Ficción', 'Editorial Sudamericana', 1967, 471, 'Una obra maestra del realismo mágico que narra la historia de la familia Buendía.', 3, 'Estante A-1'),
('Don Quijote de La Mancha', NULL, 'Miguel de Cervantes', '978-84-376-0495-4', 'Ficción', 'Editorial Planeta', 1605, 1023, 'La obra cumbre de la literatura española y universal.', 2, 'Estante A-2'),
('1984', NULL, 'George Orwell', '978-84-376-0496-1', 'Ficción', 'Secker & Warburg', 1949, 328, 'Una distopía sobre el totalitarismo y la vigilancia masiva.', 4, 'Estante B-1'),
('El principito', NULL, 'Antoine de Saint-Exupéry', '978-84-376-0497-8', 'Infantil', 'Reynal & Hitchcock', 1943, 96, 'Una fábula poética sobre la amistad, el amor y la pérdida.', 5, 'Estante C-1'),
('Sapiens: De animales a dioses', 'Una breve historia de la humanidad', 'Yuval Noah Harari', '978-84-376-0498-5', 'Historia', 'DVE', 2011, 496, 'Un recorrido por la historia de la humanidad desde los primeros humanos.', 3, 'Estante D-1'),
('El arte de la guerra', NULL, 'Sun Tzu', '978-84-376-0499-2', 'Historia', 'Editorial Gredos', 500, 112, 'Tratado militar sobre estrategia y táctica.', 2, 'Estante D-2'),
('Steve Jobs', NULL, 'Walter Isaacson', '978-84-376-0500-5', 'Biografía', 'Simon & Schuster', 2011, 656, 'La biografía autorizada del cofundador de Apple.', 2, 'Estante E-1'),
('Breve historia del tiempo', NULL, 'Stephen Hawking', '978-84-376-0501-2', 'Ciencia', 'Bantam Doubleday Dell', 1988, 256, 'Una introducción a la cosmología para el público general.', 3, 'Estante F-1'),
('El código Da Vinci', NULL, 'Dan Brown', '978-84-376-0502-9', 'Ficción', 'Doubleday', 2003, 689, 'Un thriller que combina arte, historia y misterio.', 4, 'Estante B-2'),
('Harry Potter y la piedra filosofal', NULL, 'J.K. Rowling', '978-84-376-0503-6', 'Juvenil', 'Bloomsbury', 1997, 309, 'El primer libro de la saga del joven mago Harry Potter.', 6, 'Estante C-2'),
('El Alquimista', NULL, 'Paulo Coelho', '978-84-376-0504-3', 'Ficción', 'HarperOne', 1988, 163, 'Una fábula sobre seguir los sueños y encontrar el destino.', 3, 'Estante A-3'),
('Matar a un ruiseñor', NULL, 'Harper Lee', '978-84-376-0505-0', 'Ficción', 'J.B. Lippincott & Co.', 1960, 376, 'Una novela sobre la injusticia racial en el sur de Estados Unidos.', 2, 'Estante B-3'),
('El hobbit', NULL, 'J.R.R. Tolkien', '978-84-376-0506-7', 'Juvenil', 'George Allen & Unwin', 1937, 310, 'La aventura de Bilbo Bolsón en la Tierra Media.', 4, 'Estante C-3'),
('Orgullo y prejuicio', NULL, 'Jane Austen', '978-84-376-0507-4', 'Ficción', 'T. Egerton', 1813, 432, 'Una comedia romántica sobre el amor y las clases sociales.', 3, 'Estante A-4'),
('Crónica de una muerte anunciada', NULL, 'Gabriel García Márquez', '978-84-376-0508-1', 'Ficción', 'Editorial La Oveja Negra', 1981, 122, 'Una novela corta sobre el honor y la venganza.', 2, 'Estante A-5'),
('El señor de las moscas', NULL, 'William Golding', '978-84-376-0509-8', 'Ficción', 'Faber & Faber', 1954, 224, 'Una alegoría sobre la naturaleza humana y la civilización.', 3, 'Estante B-4'),
('Fahrenheit 451', NULL, 'Ray Bradbury', '978-84-376-0510-4', 'Ficción', 'Ballantine Books', 1953, 249, 'Una distopía sobre la censura y la quema de libros.', 2, 'Estante B-5'),
('El gran Gatsby', NULL, 'F. Scott Fitzgerald', '978-84-376-0511-1', 'Ficción', 'Charles Scribner\'s Sons', 1925, 180, 'Una crítica al sueño americano en los años 20.', 3, 'Estante A-6'),
('Introducción a los algoritmos', NULL, 'Thomas H. Cormen', '978-84-376-0512-8', 'Tecnología', 'MIT Press', 2009, 1312, 'Libro de referencia sobre algoritmos y estructuras de datos.', 2, 'Estante G-1'),
('Clean Code', 'A Handbook of Agile Software Craftsmanship', 'Robert C. Martin', '978-84-376-0513-5', 'Tecnología', 'Prentice Hall', 2008, 464, 'Principios y prácticas para escribir código limpio y mantenible.', 3, 'Estante G-2');

-- Insertar algunos préstamos de prueba
INSERT INTO prestamos (usuario_id, libro_id, fecha_prestamo, fecha_limite, fecha_devolucion, observaciones, creado_por) VALUES
-- Préstamos devueltos
(3, 1, '2024-10-01', '2024-10-16', '2024-10-15', 'Préstamo completado sin problemas', 2),
(4, 2, '2024-10-05', '2024-10-20', '2024-10-18', 'Devuelto en buenas condiciones', 2),
(5, 3, '2024-10-10', '2024-10-25', '2024-10-24', 'Usuario muy responsable', 2),

-- Préstamos activos (sin fecha de devolución)
(3, 4, '2024-10-20', '2024-11-04', NULL, 'Préstamo actual - El principito', 2),
(6, 5, '2024-10-22', '2024-11-06', NULL, 'Préstamo actual - Sapiens', 2),
(7, 9, '2024-10-25', '2024-11-09', NULL, 'Préstamo actual - El código Da Vinci', 2),

-- Préstamos vencidos (fecha límite pasada, sin devolución)
(8, 11, '2024-10-01', '2024-10-16', NULL, 'Préstamo vencido - recordar al usuario', 2),
(9, 12, '2024-10-05', '2024-10-20', NULL, 'Préstamo vencido - contactar urgente', 2),

-- Más préstamos para estadísticas
(4, 6, '2024-09-15', '2024-09-30', '2024-09-28', 'Préstamo anterior completado', 2),
(5, 7, '2024-09-20', '2024-10-05', '2024-10-03', 'Biografía de Steve Jobs', 2),
(6, 8, '2024-09-25', '2024-10-10', '2024-10-08', 'Libro de ciencia', 2),
(10, 10, '2024-10-15', '2024-10-30', NULL, 'Harry Potter - préstamo activo', 2),
(3, 13, '2024-10-18', '2024-11-02', NULL, 'El hobbit - préstamo activo', 2);

-- Actualizar última actividad de usuarios
UPDATE usuarios SET ultima_actividad = NOW() WHERE id IN (1, 2);
UPDATE usuarios SET ultima_actividad = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE id IN (3, 4, 5);
UPDATE usuarios SET ultima_actividad = DATE_SUB(NOW(), INTERVAL 2 DAY) WHERE id IN (6, 7);
UPDATE usuarios SET ultima_actividad = DATE_SUB(NOW(), INTERVAL 7 DAY) WHERE id IN (8, 9, 10);