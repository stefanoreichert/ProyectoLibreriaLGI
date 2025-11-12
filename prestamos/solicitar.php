<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

// Todos los usuarios logueados pueden solicitar préstamos
verificarSesion();

$page_title = 'Solicitar Préstamo';
$errors = [];
$success = '';

// Obtener libro preseleccionado desde URL (si viene del catálogo)
$libro_preseleccionado = intval($_GET['libro_id'] ?? 0);

// Obtener días de préstamo configurados
$dias_prestamo = defined('DIAS_PRESTAMO_DEFAULT') ? DIAS_PRESTAMO_DEFAULT : 15;

if ($_POST) {
    $libro_id = intval($_POST['libro_id'] ?? 0);
    $observaciones = trim($_POST['observaciones'] ?? '');
    
    // El usuario_id es el usuario actual
    $usuario_id = $_SESSION['user_id'];
    
    // Validaciones
    if ($libro_id <= 0) {
        $errors[] = 'Debe seleccionar un libro';
    }
    
    if (empty($errors)) {
        try {
            // Verificar que el usuario existe y está activo
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND activo = 1");
            $stmt->execute([$usuario_id]);
            if (!$stmt->fetch()) {
                $errors[] = 'Usuario no válido';
            }
            
            // Verificar que el libro existe y está disponible
            $stmt = $pdo->prepare("
                SELECT l.id, l.titulo, l.stock,
                (SELECT COUNT(*) FROM prestamos p WHERE p.libro_id = l.id AND p.fecha_dev_real IS NULL) as prestados
                FROM libros l 
                WHERE l.id = ? AND l.activo = 1
            ");
            $stmt->execute([$libro_id]);
            $libro = $stmt->fetch();
            if (!$libro) {
                $errors[] = 'Libro no válido';
            } elseif ($libro['stock'] <= $libro['prestados']) {
                $errors[] = 'El libro no está disponible para préstamo en este momento';
            }
            
            // Verificar límite de libros por usuario
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE usuario_id = ? AND fecha_dev_real IS NULL");
            $stmt->execute([$usuario_id]);
            $prestamos_activos = $stmt->fetchColumn();
            if ($prestamos_activos >= MAX_LIBROS_POR_USUARIO) {
                $errors[] = "Ya tienes el máximo de " . MAX_LIBROS_POR_USUARIO . " libros prestados. Devuelve alguno para solicitar más.";
            }
            
            // Verificar que el usuario no tenga el mismo libro prestado
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE usuario_id = ? AND libro_id = ? AND fecha_dev_real IS NULL");
            $stmt->execute([$usuario_id, $libro_id]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Ya tienes este libro prestado';
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Error al validar los datos';
        }
    }
    
    // Si no hay errores, crear el préstamo
    if (empty($errors)) {
        try {
            $fecha_devolucion_limite = date('Y-m-d', strtotime("+$dias_prestamo days"));
            
            $sql = "INSERT INTO prestamos (usuario_id, libro_id, fecha_devolucion, observaciones) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $libro_id, $fecha_devolucion_limite, $observaciones]);
            
            $prestamo_id = $pdo->lastInsertId();
            $success = "¡Préstamo solicitado exitosamente! ID: #$prestamo_id. Fecha de devolución: " . date('d/m/Y', strtotime($fecha_devolucion_limite));
            
            // Limpiar formulario
            $_POST = [];
            
        } catch (PDOException $e) {
            $errors[] = 'Error al crear el préstamo: ' . $e->getMessage();
        }
    }
}

// Obtener libros disponibles
try {
    $sql = "SELECT l.id, l.titulo, l.autor, l.isbn, l.categoria, l.stock,
            (SELECT COUNT(*) FROM prestamos p WHERE p.libro_id = l.id AND p.fecha_dev_real IS NULL) as prestados,
            (l.stock - (SELECT COUNT(*) FROM prestamos p WHERE p.libro_id = l.id AND p.fecha_dev_real IS NULL)) as disponibles
            FROM libros l
            WHERE l.activo = 1 AND l.stock > 0
            HAVING disponibles > 0
            ORDER BY l.titulo";
    
    $stmt = $pdo->query($sql);
    $libros_disponibles = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $libros_disponibles = [];
    $errors[] = 'Error al cargar los libros disponibles';
}

// Obtener mis préstamos activos
try {
    $stmt = $pdo->prepare("
        SELECT p.*, l.titulo, l.autor
        FROM prestamos p
        JOIN libros l ON p.libro_id = l.id
        WHERE p.usuario_id = ? AND p.fecha_dev_real IS NULL
        ORDER BY p.fecha_devolucion ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $mis_prestamos = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $mis_prestamos = [];
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>📋 Solicitar Préstamo de Libro</h1>
    <a href="./" class="btn btn-secondary">
        <span class="btn-icon">←</span>
        Volver a Mis Préstamos
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <strong>Error:</strong>
        <ul style="margin: 10px 0 0 20px;">
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

<!-- Información de préstamos activos -->
<?php if (!empty($mis_prestamos)): ?>
<div class="info-box" style="margin-bottom: 25px; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
    <h3 style="margin: 0 0 10px 0; color: #1976D2;">📚 Tus préstamos activos (<?php echo count($mis_prestamos); ?>/<?php echo MAX_LIBROS_POR_USUARIO; ?>)</h3>
    <ul style="margin: 0; padding-left: 20px;">
        <?php foreach ($mis_prestamos as $prestamo): ?>
            <li>
                <strong><?php echo htmlspecialchars($prestamo['titulo']); ?></strong> 
                - Devolver antes del: <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion'])); ?>
                <?php if ($prestamo['fecha_devolucion'] < date('Y-m-d')): ?>
                    <span style="color: #d32f2f; font-weight: bold;">⚠️ VENCIDO</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Formulario de solicitud -->
<div class="form-container">
    <form method="POST" action="" class="form-modern">
        <div class="form-section">
            <h3>Selecciona el libro que deseas solicitar</h3>
            
            <div class="form-group">
                <label for="libro_id">Libro Disponible *</label>
                <select name="libro_id" id="libro_id" required class="form-control">
                    <option value="">-- Selecciona un libro --</option>
                    <?php foreach ($libros_disponibles as $libro): ?>
                        <option value="<?php echo $libro['id']; ?>" 
                                <?php echo ($libro_preseleccionado == $libro['id']) ? 'selected' : ''; ?>
                                data-autor="<?php echo htmlspecialchars($libro['autor']); ?>"
                                data-isbn="<?php echo htmlspecialchars($libro['isbn']); ?>"
                                data-categoria="<?php echo htmlspecialchars($libro['categoria']); ?>"
                                data-disponibles="<?php echo $libro['disponibles']; ?>">
                            <?php echo htmlspecialchars($libro['titulo']); ?> 
                            (<?php echo $libro['disponibles']; ?> disponible<?php echo $libro['disponibles'] > 1 ? 's' : ''; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Información del libro seleccionado -->
            <div id="libro-info" style="display: none; margin-top: 15px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
                <h4 style="margin-top: 0;">Información del libro:</h4>
                <p><strong>Autor:</strong> <span id="info-autor">-</span></p>
                <p><strong>ISBN:</strong> <span id="info-isbn">-</span></p>
                <p><strong>Categoría:</strong> <span id="info-categoria">-</span></p>
                <p><strong>Copias disponibles:</strong> <span id="info-disponibles">-</span></p>
                <p style="margin-bottom: 0;"><strong>Fecha de devolución:</strong> <span id="fecha_devolucion_display">-</span></p>
            </div>
            
            <div class="form-group">
                <label for="observaciones">Observaciones (opcional)</label>
                <textarea name="observaciones" id="observaciones" rows="3" class="form-control" 
                          placeholder="Ej: Necesito este libro para un proyecto..."><?php echo htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <span class="btn-icon">✓</span>
                Solicitar Préstamo
            </button>
            <a href="./" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<!-- Listado de libros disponibles -->
<div style="margin-top: 40px;">
    <h2>📚 Libros Disponibles (<?php echo count($libros_disponibles); ?>)</h2>
    
    <?php if (empty($libros_disponibles)): ?>
        <div class="alert alert-info">
            No hay libros disponibles en este momento. Por favor, intenta más tarde.
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categoría</th>
                        <th>Disponibles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($libros_disponibles as $libro): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong>
                                <br><small class="text-muted">ISBN: <?php echo htmlspecialchars($libro['isbn']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                            <td><?php echo htmlspecialchars($libro['categoria']); ?></td>
                            <td>
                                <span class="stock-badge stock-available">
                                    <?php echo $libro['disponibles']; ?> de <?php echo $libro['stock']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const libroSelect = document.getElementById('libro_id');
    const libroInfo = document.getElementById('libro-info');
    const diasPrestamo = <?php echo $dias_prestamo; ?>;
    
    function mostrarInfoLibro() {
        if (libroSelect.value) {
            const option = libroSelect.options[libroSelect.selectedIndex];
            document.getElementById('info-autor').textContent = option.dataset.autor;
            document.getElementById('info-isbn').textContent = option.dataset.isbn;
            document.getElementById('info-categoria').textContent = option.dataset.categoria;
            document.getElementById('info-disponibles').textContent = option.dataset.disponibles;
            
            // Calcular fecha de devolución
            const hoy = new Date();
            hoy.setDate(hoy.getDate() + diasPrestamo);
            const fechaDevolucion = hoy.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            document.getElementById('fecha_devolucion_display').textContent = fechaDevolucion;
            
            libroInfo.style.display = 'block';
        } else {
            libroInfo.style.display = 'none';
        }
    }
    
    libroSelect.addEventListener('change', mostrarInfoLibro);
    
    // Si hay un libro preseleccionado, mostrar su información automáticamente
    if (libroSelect.value) {
        mostrarInfoLibro();
    }
            libroInfo.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
