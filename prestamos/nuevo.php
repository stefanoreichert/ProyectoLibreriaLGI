<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Verificar permisos
if (!isBibliotecario()) {
    header('Location: ../dashboard.php');
    exit();
}

$page_title = 'Nuevo Préstamo';
$errors = [];
$success = '';

// Obtener usuario preseleccionado si viene de la URL
$usuario_preseleccionado = intval($_GET['usuario_id'] ?? 0);

if ($_POST) {
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    $libro_id = intval($_POST['libro_id'] ?? 0);
    $dias_prestamo = intval($_POST['dias_prestamo'] ?? DIAS_PRESTAMO_DEFAULT);
    $observaciones = trim($_POST['observaciones'] ?? '');
    
    // Validaciones
    if ($usuario_id <= 0) $errors[] = 'Debe seleccionar un usuario';
    if ($libro_id <= 0) $errors[] = 'Debe seleccionar un libro';
    if ($dias_prestamo <= 0 || $dias_prestamo > 90) $errors[] = 'Los días de préstamo deben estar entre 1 y 90';
    
    if (empty($errors)) {
        try {
            // Verificar que el usuario existe y está activo
            $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE id = ? AND activo = 1");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch();
            if (!$usuario) {
                $errors[] = 'Usuario no válido';
            }
            
            // Verificar que el libro existe y está disponible
            $stmt = $pdo->prepare("
                SELECT l.id, l.titulo, l.stock,
                (SELECT COUNT(*) FROM prestamos p WHERE p.libro_id = l.id AND p.fecha_devolucion IS NULL) as prestados
                FROM libros l 
                WHERE l.id = ? AND l.activo = 1
            ");
            $stmt->execute([$libro_id]);
            $libro = $stmt->fetch();
            if (!$libro) {
                $errors[] = 'Libro no válido';
            } elseif ($libro['stock'] <= $libro['prestados']) {
                $errors[] = 'El libro no está disponible para préstamo';
            }
            
            // Verificar límite de libros por usuario
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE usuario_id = ? AND fecha_devolucion IS NULL");
            $stmt->execute([$usuario_id]);
            $prestamos_activos = $stmt->fetchColumn();
            if ($prestamos_activos >= MAX_LIBROS_POR_USUARIO) {
                $errors[] = "El usuario ya tiene el máximo de " . MAX_LIBROS_POR_USUARIO . " libros prestados";
            }
            
            // Verificar que el usuario no tenga el mismo libro prestado
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE usuario_id = ? AND libro_id = ? AND fecha_devolucion IS NULL");
            $stmt->execute([$usuario_id, $libro_id]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'El usuario ya tiene este libro prestado';
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Error al validar los datos';
        }
    }
    
    // Si no hay errores, crear el préstamo
    if (empty($errors)) {
        try {
            $fecha_limite = date('Y-m-d', strtotime("+$dias_prestamo days"));
            
            $sql = "INSERT INTO prestamos (usuario_id, libro_id, fecha_prestamo, fecha_limite, observaciones, creado_por) 
                    VALUES (?, ?, CURDATE(), ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $libro_id, $fecha_limite, $observaciones, $_SESSION['user_id']]);
            
            $prestamo_id = $pdo->lastInsertId();
            $success = "Préstamo creado exitosamente. ID: #$prestamo_id";
            
            // Limpiar formulario
            $_POST = [];
            $usuario_preseleccionado = 0;
            
        } catch (PDOException $e) {
            $errors[] = 'Error al crear el préstamo: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>📋 Nuevo Préstamo</h1>
    <div class="header-actions">
        <a href="index.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Volver a Préstamos
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
        <div style="margin-top: 10px;">
            <a href="index.php" class="btn btn-sm btn-primary">Ver préstamos</a>
            <button onclick="window.print()" class="btn btn-sm btn-secondary">Imprimir comprobante</button>
        </div>
    </div>
<?php endif; ?>

<form method="POST" action="" class="form-container" id="prestamo-form">
    <div class="form-section">
        <h3>Seleccionar Usuario</h3>
        <div class="form-group">
            <label for="usuario_id">Usuario *</label>
            <div class="autocomplete-container">
                <input type="text" id="usuario_search" placeholder="Buscar usuario por nombre..." 
                       value="<?php echo $usuario_preseleccionado > 0 ? '' : ''; ?>" class="autocomplete-input">
                <input type="hidden" id="usuario_id" name="usuario_id" 
                       value="<?php echo $usuario_preseleccionado; ?>" required>
                <div id="usuario_results" class="autocomplete-results"></div>
            </div>
            <div id="usuario_info" class="selected-info"></div>
        </div>
    </div>
    
    <div class="form-section">
        <h3>Seleccionar Libro</h3>
        <div class="form-group">
            <label for="libro_id">Libro *</label>
            <div class="autocomplete-container">
                <input type="text" id="libro_search" placeholder="Buscar libro por título o ISBN..." class="autocomplete-input">
                <input type="hidden" id="libro_id" name="libro_id" required>
                <div id="libro_results" class="autocomplete-results"></div>
            </div>
            <div id="libro_info" class="selected-info"></div>
        </div>
    </div>
    
    <div class="form-section">
        <h3>Detalles del Préstamo</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="dias_prestamo">Días de Préstamo *</label>
                <input type="number" id="dias_prestamo" name="dias_prestamo" 
                       value="<?php echo $_POST['dias_prestamo'] ?? DIAS_PRESTAMO_DEFAULT; ?>" 
                       min="1" max="90" required>
                <small class="form-help">Máximo 90 días</small>
            </div>
            
            <div class="form-group">
                <label for="fecha_limite">Fecha Límite</label>
                <input type="date" id="fecha_limite" readonly class="readonly-input">
            </div>
        </div>
        
        <div class="form-group">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="3" 
                      placeholder="Observaciones adicionales sobre el préstamo..."><?php echo htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
            <span class="btn-icon">💾</span>
            Crear Préstamo
        </button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<script>
// Calcular fecha límite automáticamente
document.getElementById('dias_prestamo').addEventListener('input', function() {
    const dias = parseInt(this.value);
    if (dias > 0) {
        const fecha = new Date();
        fecha.setDate(fecha.getDate() + dias);
        document.getElementById('fecha_limite').value = fecha.toISOString().split('T')[0];
    }
});

// Trigger inicial
document.getElementById('dias_prestamo').dispatchEvent(new Event('input'));

// Autocomplete para usuarios
let usuarioTimeout;
document.getElementById('usuario_search').addEventListener('input', function() {
    clearTimeout(usuarioTimeout);
    const query = this.value;
    
    if (query.length >= 2) {
        usuarioTimeout = setTimeout(() => {
            fetch(`ajax_validar.php?action=buscar_usuarios&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    const results = document.getElementById('usuario_results');
                    results.innerHTML = '';
                    
                    data.resultados.forEach(usuario => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.innerHTML = `
                            <strong>${usuario.nombre}</strong><br>
                            <small>${usuario.email} - Préstamos activos: ${usuario.prestamos_activos}</small>
                        `;
                        div.onclick = () => selectUsuario(usuario);
                        results.appendChild(div);
                    });
                    
                    results.style.display = data.resultados.length > 0 ? 'block' : 'none';
                });
        }, 300);
    } else {
        document.getElementById('usuario_results').style.display = 'none';
    }
});

// Autocomplete para libros
let libroTimeout;
document.getElementById('libro_search').addEventListener('input', function() {
    clearTimeout(libroTimeout);
    const query = this.value;
    
    if (query.length >= 2) {
        libroTimeout = setTimeout(() => {
            fetch(`ajax_validar.php?action=buscar_libros&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    const results = document.getElementById('libro_results');
                    results.innerHTML = '';
                    
                    data.resultados.forEach(libro => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.innerHTML = `
                            <strong>${libro.titulo}</strong><br>
                            <small>${libro.autor} - Disponibles: ${libro.disponibles}/${libro.stock}</small>
                        `;
                        
                        if (libro.disponibles <= 0) {
                            div.className += ' disabled';
                        } else {
                            div.onclick = () => selectLibro(libro);
                        }
                        
                        results.appendChild(div);
                    });
                    
                    results.style.display = data.resultados.length > 0 ? 'block' : 'none';
                });
        }, 300);
    } else {
        document.getElementById('libro_results').style.display = 'none';
    }
});

function selectUsuario(usuario) {
    if (usuario.prestamos_activos >= <?php echo MAX_LIBROS_POR_USUARIO; ?>) {
        alert('Este usuario ya tiene el máximo de libros prestados permitidos.');
        return;
    }
    
    document.getElementById('usuario_search').value = usuario.nombre;
    document.getElementById('usuario_id').value = usuario.id;
    document.getElementById('usuario_results').style.display = 'none';
    
    document.getElementById('usuario_info').innerHTML = `
        <div class="info-card">
            <strong>${usuario.nombre}</strong><br>
            Email: ${usuario.email}<br>
            Préstamos activos: ${usuario.prestamos_activos}/${<?php echo MAX_LIBROS_POR_USUARIO; ?>}
        </div>
    `;
    
    validateForm();
}

function selectLibro(libro) {
    document.getElementById('libro_search').value = libro.titulo;
    document.getElementById('libro_id').value = libro.id;
    document.getElementById('libro_results').style.display = 'none';
    
    document.getElementById('libro_info').innerHTML = `
        <div class="info-card">
            <strong>${libro.titulo}</strong><br>
            Autor: ${libro.autor}<br>
            ISBN: ${libro.isbn}<br>
            Disponibles: ${libro.disponibles} de ${libro.stock}
        </div>
    `;
    
    validateForm();
}

function validateForm() {
    const usuarioId = document.getElementById('usuario_id').value;
    const libroId = document.getElementById('libro_id').value;
    const submitBtn = document.getElementById('submit-btn');
    
    submitBtn.disabled = !(usuarioId && libroId);
}

// Cerrar dropdowns al hacer click afuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.autocomplete-container')) {
        document.getElementById('usuario_results').style.display = 'none';
        document.getElementById('libro_results').style.display = 'none';
    }
});

// Cargar usuario preseleccionado
<?php if ($usuario_preseleccionado > 0): ?>
    fetch(`ajax_validar.php?action=get_usuario&id=<?php echo $usuario_preseleccionado; ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.usuario) {
                selectUsuario(data.usuario);
            }
        });
<?php endif; ?>
</script>

<?php 
$include_search_js = true;
include '../includes/footer.php'; 
?>