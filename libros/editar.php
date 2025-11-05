<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Verificar permisos
if (!isBibliotecario()) {
    header('Location: ../dashboard.php');
    exit();
}

$page_title = 'Editar Libro';
$errors = [];
$success = '';
$libro = null;

// Obtener ID del libro
$libro_id = intval($_GET['id'] ?? 0);

if ($libro_id <= 0) {
    header('Location: index.php');
    exit();
}

// Obtener datos del libro
try {
    $stmt = $pdo->prepare("SELECT * FROM libros WHERE id = ? AND activo = 1");
    $stmt->execute([$libro_id]);
    $libro = $stmt->fetch();
    
    if (!$libro) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    $errors[] = 'Error al obtener los datos del libro';
}

if ($_POST && $libro) {
    // Validar campos requeridos
    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $editorial = trim($_POST['editorial'] ?? '');
    $año_publicacion = intval($_POST['año_publicacion'] ?? 0);
    $paginas = intval($_POST['paginas'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    
    // Validaciones
    if (empty($titulo)) $errors[] = 'El título es requerido';
    if (empty($autor)) $errors[] = 'El autor es requerido';
    if (empty($isbn)) $errors[] = 'El ISBN es requerido';
    if (empty($categoria)) $errors[] = 'La categoría es requerida';
    if ($stock < 0) $errors[] = 'El stock no puede ser negativo';
    
    // Validar ISBN único (excepto el libro actual)
    if (!empty($isbn)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM libros WHERE isbn = ? AND id != ? AND activo = 1");
            $stmt->execute([$isbn, $libro_id]);
            if ($stmt->fetch()) {
                $errors[] = 'Ya existe otro libro con este ISBN';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error al validar ISBN';
        }
    }
    
    // Si no hay errores, actualizar
    if (empty($errors)) {
        try {
            $sql = "UPDATE libros SET titulo = ?, subtitulo = ?, autor = ?, isbn = ?, 
                    categoria = ?, editorial = ?, año_publicacion = ?, paginas = ?, 
                    descripcion = ?, stock = ?, ubicacion = ? WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $titulo, $subtitulo, $autor, $isbn, $categoria, $editorial,
                $año_publicacion > 0 ? $año_publicacion : null,
                $paginas > 0 ? $paginas : null,
                $descripcion, $stock, $ubicacion, $libro_id
            ]);
            
            $success = 'Libro actualizado exitosamente';
            
            // Recargar datos del libro
            $stmt = $pdo->prepare("SELECT * FROM libros WHERE id = ?");
            $stmt->execute([$libro_id]);
            $libro = $stmt->fetch();
            
        } catch (PDOException $e) {
            $errors[] = 'Error al actualizar el libro: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>📚 Editar Libro</h1>
    <div class="header-actions">
        <a href="index.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Volver a Libros
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if ($libro): ?>
<form method="POST" action="" class="form-container">
    <div class="form-grid">
        <div class="form-group">
            <label for="titulo">Título *</label>
            <input type="text" id="titulo" name="titulo" required 
                   value="<?php echo htmlspecialchars($_POST['titulo'] ?? $libro['titulo']); ?>">
        </div>
        
        <div class="form-group">
            <label for="subtitulo">Subtítulo</label>
            <input type="text" id="subtitulo" name="subtitulo" 
                   value="<?php echo htmlspecialchars($_POST['subtitulo'] ?? $libro['subtitulo']); ?>">
        </div>
        
        <div class="form-group">
            <label for="autor">Autor *</label>
            <input type="text" id="autor" name="autor" required 
                   value="<?php echo htmlspecialchars($_POST['autor'] ?? $libro['autor']); ?>">
        </div>
        
        <div class="form-group">
            <label for="isbn">ISBN *</label>
            <input type="text" id="isbn" name="isbn" required 
                   value="<?php echo htmlspecialchars($_POST['isbn'] ?? $libro['isbn']); ?>">
        </div>
        
        <div class="form-group">
            <label for="categoria">Categoría *</label>
            <select id="categoria" name="categoria" required>
                <option value="">Seleccionar categoría</option>
                <?php 
                $categorias = ['Ficción', 'No Ficción', 'Ciencia', 'Historia', 'Biografía', 'Tecnología', 'Arte', 'Educación', 'Infantil', 'Juvenil'];
                $categoria_actual = $_POST['categoria'] ?? $libro['categoria'];
                foreach ($categorias as $cat): 
                ?>
                    <option value="<?php echo $cat; ?>" <?php echo $categoria_actual === $cat ? 'selected' : ''; ?>>
                        <?php echo $cat; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="editorial">Editorial</label>
            <input type="text" id="editorial" name="editorial" 
                   value="<?php echo htmlspecialchars($_POST['editorial'] ?? $libro['editorial']); ?>">
        </div>
        
        <div class="form-group">
            <label for="año_publicacion">Año de Publicación</label>
            <input type="number" id="año_publicacion" name="año_publicacion" 
                   min="1000" max="<?php echo date('Y') + 1; ?>"
                   value="<?php echo htmlspecialchars($_POST['año_publicacion'] ?? $libro['año_publicacion']); ?>">
        </div>
        
        <div class="form-group">
            <label for="paginas">Número de Páginas</label>
            <input type="number" id="paginas" name="paginas" min="1"
                   value="<?php echo htmlspecialchars($_POST['paginas'] ?? $libro['paginas']); ?>">
        </div>
        
        <div class="form-group">
            <label for="stock">Stock *</label>
            <input type="number" id="stock" name="stock" min="0" required 
                   value="<?php echo htmlspecialchars($_POST['stock'] ?? $libro['stock']); ?>">
        </div>
        
        <div class="form-group">
            <label for="ubicacion">Ubicación Física</label>
            <input type="text" id="ubicacion" name="ubicacion" 
                   value="<?php echo htmlspecialchars($_POST['ubicacion'] ?? $libro['ubicacion']); ?>"
                   placeholder="Ej: Estante A-3, Sección Historia">
        </div>
    </div>
    
    <div class="form-group">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="4" 
                  placeholder="Breve descripción del libro..."><?php echo htmlspecialchars($_POST['descripcion'] ?? $libro['descripcion']); ?></textarea>
    </div>
    
    <div class="book-info">
        <p><strong>Fecha de registro:</strong> <?php echo formatDateTime($libro['fecha_registro']); ?></p>
        <p><strong>ID del libro:</strong> #<?php echo $libro['id']; ?></p>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <span class="btn-icon">💾</span>
            Actualizar Libro
        </button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>