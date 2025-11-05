-- Insertar libros de ejemplo
USE libreria;

INSERT INTO libros (titulo, subtitulo, autor, isbn, editorial, ano_publicacion, paginas, categoria, descripcion, stock, ubicacion, activo) VALUES
('Don Quijote de la Mancha', 'El Ingenioso Hidalgo', 'Miguel de Cervantes', '978-84-376-0494-7', 'Editorial Planeta', 1605, 863, 'Literatura Clásica', 'La obra maestra de la literatura española que narra las aventuras del ingenioso hidalgo Don Quijote y su fiel escudero Sancho Panza.', 3, 'Estante A-1', TRUE),

('Cien años de soledad', NULL, 'Gabriel García Márquez', '978-84-376-0495-4', 'Editorial Sudamericana', 1967, 471, 'Realismo Mágico', 'Una de las obras cumbre del realismo mágico que narra la historia de la familia Buendía a lo largo de siete generaciones.', 2, 'Estante B-2', TRUE),

('1984', NULL, 'George Orwell', '978-84-376-0496-1', 'Secker & Warburg', 1949, 328, 'Distopía', 'Una novela distópica que presenta un futuro totalitario donde el Gran Hermano lo controla todo.', 4, 'Estante C-1', TRUE),

('El principito', NULL, 'Antoine de Saint-Exupéry', '978-84-376-0497-8', 'Reynal & Hitchcock', 1943, 96, 'Literatura Infantil', 'Una bella historia sobre un pequeño príncipe que viaja por diferentes planetas y aprende sobre la vida y el amor.', 5, 'Estante D-1', TRUE),

('Harry Potter y la piedra filosofal', NULL, 'J.K. Rowling', '978-84-376-0498-5', 'Bloomsbury', 1997, 223, 'Fantasía', 'La primera aventura del joven mago Harry Potter en el mundo mágico de Hogwarts.', 3, 'Estante E-1', TRUE),

('El código Da Vinci', NULL, 'Dan Brown', '978-84-376-0499-2', 'Doubleday', 2003, 689, 'Thriller', 'Un thriller que combina arte, historia y religión en una trama llena de misterios y códigos secretos.', 2, 'Estante F-1', TRUE),

('Orgullo y prejuicio', NULL, 'Jane Austen', '978-84-376-0500-5', 'T. Egerton', 1813, 432, 'Romance', 'Una novela romántica que explora temas de amor, reputación y clase social en la Inglaterra del siglo XIX.', 3, 'Estante A-2', TRUE),

('El señor de los anillos', 'La comunidad del anillo', 'J.R.R. Tolkien', '978-84-376-0501-2', 'George Allen & Unwin', 1954, 576, 'Fantasía épica', 'La primera parte de la épica historia de la Tierra Media y la lucha contra el mal.', 2, 'Estante E-2', TRUE),

('Introducción a la Programación', 'Conceptos básicos y algoritmos', 'María González', '978-84-376-0502-9', 'Editorial Técnica', 2020, 345, 'Informática', 'Un libro completo para aprender los fundamentos de la programación y el desarrollo de algoritmos.', 4, 'Estante G-1', TRUE),

('Historia Universal', 'Desde los orígenes hasta el siglo XXI', 'Carlos Rodríguez', '978-84-376-0503-6', 'Editorial Historia', 2019, 892, 'Historia', 'Un recorrido completo por la historia de la humanidad desde sus orígenes hasta la actualidad.', 3, 'Estante H-1', TRUE);

-- Verificar que se insertaron correctamente
SELECT COUNT(*) as total_libros_insertados FROM libros WHERE activo = TRUE;

SELECT 'Libros de ejemplo insertados correctamente' as resultado;