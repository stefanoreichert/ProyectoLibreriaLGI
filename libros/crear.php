<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Verificar permisos
if (!isBibliotecario()) {
    header('Location: ../dashboard.php');
    exit();
}

$page_title = 'Crear Nuevo Libro';
$errors = [];
$success = '';

if ($_POST) {
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
    
    // Validar ISBN único
    if (!empty($isbn)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM libros WHERE isbn = ? AND activo = 1");
            $stmt->execute([$isbn]);
            if ($stmt->fetch()) {
                $errors[] = 'Ya existe un libro con este ISBN';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error al validar ISBN';
        }
    }
    
    // Si no hay errores, insertar
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO libros (titulo, subtitulo, autor, isbn, categoria, editorial, 
                    año_publicacion, paginas, descripcion, stock, ubicacion, fecha_registro, activo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $titulo, $subtitulo, $autor, $isbn, $categoria, $editorial,
                $año_publicacion > 0 ? $año_publicacion : null,
                $paginas > 0 ? $paginas : null,
                $descripcion, $stock, $ubicacion
            ]);
            
            $success = 'Libro creado exitosamente';
            
            // Limpiar formulario
            $_POST = [];
            
        } catch (PDOException $e) {
            $errors[] = 'Error al crear el libro: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>📚 Crear Nuevo Libro</h1>
    <a href="index.php" class="btn btn-secondary">
        <span class="btn-icon">←</span>
        Volver a Libros
    </a>
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
        <a href="index.php" class="btn btn-sm btn-primary" style="margin-left: 10px;">Ver todos los libros</a>
    </div>
<?php endif; ?>

<form method="POST" action="" class="form-container">
    <div class="form-grid">
        <div class="form-group">
            <label for="titulo">Título *</label>
            <input type="text" id="titulo" name="titulo" required 
                   value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="subtitulo">Subtítulo</label>
            <input type="text" id="subtitulo" name="subtitulo" 
                   value="<?php echo htmlspecialchars($_POST['subtitulo'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="autor">Autor *</label>
            <input type="text" id="autor" name="autor" required 
                   value="<?php echo htmlspecialchars($_POST['autor'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="isbn">ISBN *</label>
            <input type="text" id="isbn" name="isbn" required 
                   value="<?php echo htmlspecialchars($_POST['isbn'] ?? ''); ?>"
                   placeholder="978-0-123456-78-9">
        </div>
        
        <div class="form-group">
            <label for="categoria">Categoría *</label>
            <select id="categoria" name="categoria" required>
                <option value="">Seleccionar categoría</option>
                <option value="Ficción" <?php echo ($_POST['categoria'] ?? '') === 'Ficción' ? 'selected' : ''; ?>>Ficción</option>
                <option value="No Ficción" <?php echo ($_POST['categoria'] ?? '') === 'No Ficción' ? 'selected' : ''; ?>>No Ficción</option>
                <option value="Ciencia" <?php echo ($_POST['categoria'] ?? '') === 'Ciencia' ? 'selected' : ''; ?>>Ciencia</option>
                <option value="Historia" <?php echo ($_POST['categoria'] ?? '') === 'Historia' ? 'selected' : ''; ?>>Historia</option>
                <option value="Biografía" <?php echo ($_POST['categoria'] ?? '') === 'Biografía' ? 'selected' : ''; ?>>Biografía</option>
                <option value="Tecnología" <?php echo ($_POST['categoria'] ?? '') === 'Tecnología' ? 'selected' : ''; ?>>Tecnología</option>
                <option value="Arte" <?php echo ($_POST['categoria'] ?? '') === 'Arte' ? 'selected' : ''; ?>>Arte</option>
                <option value="Educación" <?php echo ($_POST['categoria'] ?? '') === 'Educación' ? 'selected' : ''; ?>>Educación</option>
                <option value="Infantil" <?php echo ($_POST['categoria'] ?? '') === 'Infantil' ? 'selected' : ''; ?>>Infantil</option>
                <option value="Juvenil" <?php echo ($_POST['categoria'] ?? '') === 'Juvenil' ? 'selected' : ''; ?>>Juvenil</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="editorial">Editorial</label>
            <input type="text" id="editorial" name="editorial" 
                   value="<?php echo htmlspecialchars($_POST['editorial'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="año_publicacion">Año de Publicación</label>
            <input type="number" id="año_publicacion" name="año_publicacion" 
                   min="1000" max="<?php echo date('Y') + 1; ?>"
                   value="<?php echo htmlspecialchars($_POST['año_publicacion'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="paginas">Número de Páginas</label>
            <input type="number" id="paginas" name="paginas" min="1"
                   value="<?php echo htmlspecialchars($_POST['paginas'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="stock">Stock *</label>
            <input type="number" id="stock" name="stock" min="0" required 
                   value="<?php echo htmlspecialchars($_POST['stock'] ?? '1'); ?>">
        </div>
        
        <div class="form-group">
            <label for="ubicacion">Ubicación Física</label>
            <input type="text" id="ubicacion" name="ubicacion" 
                   value="<?php echo htmlspecialchars($_POST['ubicacion'] ?? ''); ?>"
                   placeholder="Ej: Estante A-3, Sección Historia">
        </div>
    </div>
    
    <div class="form-group">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="4" 
                  placeholder="Breve descripción del libro..."><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <span class="btn-icon">💾</span>
            Crear Libro
        </button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php include '../includes/footer.php'; ?>