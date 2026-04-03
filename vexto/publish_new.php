<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'db_connect.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$edit_mode = false;
$property = null;

// Verificar si estamos editando
if (isset($_GET['edit'])) {
    $property_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND user_id = ?");
    $stmt->execute([$property_id, $user_id]);
    $property = $stmt->fetch();
    if ($property) {
        $edit_mode = true;
    }
}

$can_publish = $user['propiedades_publicadas'] < $user['max_propiedades'] || $edit_mode;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_publish) {
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $tipo_operacion = $_POST['tipo_operacion'] ?? '';
    $tipo_propiedad = $_POST['tipo_propiedad'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $lat = $_POST['lat'] ?? 0;
    $lng = $_POST['lng'] ?? 0;
    $habitaciones = $_POST['habitaciones'] ?? 0;
    $banos = $_POST['banos'] ?? 0;
    $area_m2 = $_POST['area_m2'] ?? 0;
    
    $imagen_url = null;
    $imagen_tipo = null;
    
    // Procesar imagen si se proporciona
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $maxImageSize = 10 * 1024 * 1024; // 10 MB
        if ($_FILES['imagen']['size'] > $maxImageSize) {
            die('Error al publicar: la imagen supera el tamaño máximo permitido de 10MB.');
        }

        $uploadDir = __DIR__ . '/publicaciones/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['imagen']['name']));
        $targetFile = $uploadDir . $safeName;

        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile)) {
            die('Error al publicar: no se pudo mover el archivo de imagen.');
        }

        $imagen_url = 'publicaciones/' . $safeName;
        $imagen_tipo = $_FILES['imagen']['type'];
    } elseif ($edit_mode && $property) {
        // Si estamos editando y no hay nueva imagen, mantener la anterior
        $imagen_url = $property['imagen_url'];
        $imagen_tipo = $property['imagen_tipo'];
    }

    try {
        $pdo->beginTransaction();
        
        if ($edit_mode && $property) {
            // Actualizar propiedad existente
            $stmt = $pdo->prepare("UPDATE properties SET titulo = ?, descripcion = ?, precio = ?, tipo_operacion = ?, tipo_propiedad = ?, ubicacion = ?, latitud = ?, longitud = ?, habitaciones = ?, banos = ?, area_m2 = ?, imagen_url = ?, imagen_tipo = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$titulo, $descripcion, $precio, $tipo_operacion, $tipo_propiedad, $ubicacion, $lat, $lng, $habitaciones, $banos, $area_m2, $imagen_url, $imagen_tipo, $property['id'], $user_id]);
        } else {
            // Crear nueva propiedad
            $stmt = $pdo->prepare("INSERT INTO properties (user_id, titulo, descripcion, precio, tipo_operacion, tipo_propiedad, ubicacion, latitud, longitud, habitaciones, banos, area_m2, imagen_url, imagen_tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $titulo, $descripcion, $precio, $tipo_operacion, $tipo_propiedad, $ubicacion, $lat, $lng, $habitaciones, $banos, $area_m2, $imagen_url, $imagen_tipo]);
            
            $stmt = $pdo->prepare("UPDATE users SET propiedades_publicadas = propiedades_publicadas + 1 WHERE id = ?");
            $stmt->execute([$user_id]);
        }
        
        $pdo->commit();
        header("Location: my_publications.php?success=1");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            try {
                $pdo->rollBack();
            } catch (Exception $rollbackException) {
                // Ignorar rollback si la conexión se perdió antes de poder revertir
            }
        }
        die("Error al publicar: " . $e->getMessage());
    }
}
?>

<div class="main-container" style="max-width: 800px; margin: 40px auto; flex-direction: column;">
    <div class="filter-card" style="width: 100%; position: static;">
        <h1 style="margin-bottom: 30px; font-size: 2rem; font-weight: 800;">
            <?php echo $edit_mode ? 'Editar Publicación' : 'Publicar Propiedad'; ?>
        </h1>
        
        <?php if (!$can_publish && !$edit_mode): ?>
            <div style="background: #fff5f5; border: 1px solid #feb2b2; padding: 20px; border-radius: 8px; color: #c53030; margin-bottom: 20px;">
                <h3>Límite alcanzado</h3>
                <p>Has publicado <?php echo $user['propiedades_publicadas']; ?> de <?php echo $user['max_propiedades']; ?> permitidas. Mejora tu plan en configuración.</p>
                <a href="settings.php" class="btn btn-primary" style="margin-top: 15px;">Mejorar Plan</a>
            </div>
        <?php else: ?>
            <form action="publish_new.php<?php echo $edit_mode ? '?edit=' . $property['id'] : ''; ?>" method="POST" enctype="multipart/form-data">
                <div class="filter-group">
                    <h3>Información Básica</h3>
                    <label>Título de la publicación</label>
                    <input type="text" name="titulo" placeholder="Ej: Hermosa casa en la playa" value="<?php echo $property ? htmlspecialchars($property['titulo']) : ''; ?>" required>
                    
                    <label>Descripción detallada</label>
                    <textarea name="descripcion" rows="5" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-color);" required><?php echo $property ? htmlspecialchars($property['descripcion']) : ''; ?></textarea>
                </div>

                <div class="filter-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label>Precio ($)</label>
                        <input type="number" name="precio" step="0.01" value="<?php echo $property ? $property['precio'] : ''; ?>" required>
                    </div>
                    <div>
                        <label>Tipo de Operación</label>
                        <select name="tipo_operacion" required>
                            <option value="venta" <?php echo ($property && $property['tipo_operacion'] === 'venta') ? 'selected' : ''; ?>>Venta</option>
                            <option value="alquiler" <?php echo ($property && $property['tipo_operacion'] === 'alquiler') ? 'selected' : ''; ?>>Alquiler</option>
                        </select>
                    </div>
                </div>

                <div class="filter-group" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div>
                        <label>Tipo de Propiedad</label>
                        <select name="tipo_propiedad" required>
                            <option value="casa" <?php echo ($property && $property['tipo_propiedad'] === 'casa') ? 'selected' : ''; ?>>Casa</option>
                            <option value="apartamento" <?php echo ($property && $property['tipo_propiedad'] === 'apartamento') ? 'selected' : ''; ?>>Apartamento</option>
                            <option value="local" <?php echo ($property && $property['tipo_propiedad'] === 'local') ? 'selected' : ''; ?>>Local Comercial</option>
                            <option value="terreno" <?php echo ($property && $property['tipo_propiedad'] === 'terreno') ? 'selected' : ''; ?>>Terreno</option>
                        </select>
                    </div>
                    <div>
                        <label>Habitaciones</label>
                        <input type="number" name="habitaciones" value="<?php echo $property ? $property['habitaciones'] : '0'; ?>">
                    </div>
                    <div>
                        <label>Baños</label>
                        <input type="number" name="banos" value="<?php echo $property ? $property['banos'] : '0'; ?>">
                    </div>
                </div>

                <div class="filter-group">
                    <label>Área (m²)</label>
                    <input type="number" name="area_m2" step="0.01" value="<?php echo $property ? $property['area_m2'] : ''; ?>" required>
                </div>

                <div class="filter-group">
                    <h3>Ubicación Exacta</h3>
                    <label>Dirección o Zona</label>
                    <input type="text" name="ubicacion" placeholder="Ej: Calle 123, Ciudad" value="<?php echo $property ? htmlspecialchars($property['ubicacion']) : ''; ?>" required>
                    
                    <label>Selecciona en el mapa</label>
                    <div id="map" style="height: 300px;"></div>
                    <input type="hidden" name="lat" id="lat" value="<?php echo $property ? $property['latitud'] : '18.4861'; ?>">
                    <input type="hidden" name="lng" id="lng" value="<?php echo $property ? $property['longitud'] : '-69.9312'; ?>">
                </div>

                <div class="filter-group">
                    <h3>Multimedia</h3>
                    <label>Imagen Principal <?php echo $edit_mode ? '(Dejar vacío para mantener la actual)' : ''; ?></label>
                    <input type="file" id="imagenFile" name="imagen" accept="image/*" <?php echo !$edit_mode ? 'required' : ''; ?>>

                    <div id="imagenPreview" style="margin-top: 15px; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; width: 100%; max-height: 300px; display: none; cursor: pointer;">
                        <img id="imagenPreviewImg" src="" alt="Vista previa" style="width: 100%; height: auto; display: block; object-fit: cover;" />
                    </div>
                    
                    <?php if ($edit_mode && $property && ($property['imagen_url'] || $property['imagen'])): ?>
                        <div style="margin-top: 15px; padding: 10px; background: var(--secondary-bg); border-radius: 8px;">
                            <p style="font-size: 0.9rem; margin-bottom: 10px;">Imagen actual:</p>
                            <?php if (!empty($property['imagen_url']) && file_exists($property['imagen_url'])): ?>
                                <img src="<?php echo htmlspecialchars($property['imagen_url']); ?>" alt="Imagen actual" style="max-width: 100%; max-height: 200px; border-radius: 4px;">
                            <?php else: ?>
                                <img src="data:<?php echo htmlspecialchars($property['imagen_tipo']); ?>;base64,<?php echo base64_encode($property['imagen']); ?>" alt="Imagen actual" style="max-width: 100%; max-height: 200px; border-radius: 4px;">
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem;">
                    <?php echo $edit_mode ? 'Actualizar Publicación' : 'Publicar Proyecto'; ?>
                </button>
                <?php if ($edit_mode): ?>
                    <a href="my_publications.php" class="btn btn-outline" style="width: 100%; padding: 15px; font-size: 1.1rem; margin-top: 10px; text-align: center;">Cancelar</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lat = parseFloat(document.getElementById('lat').value) || 18.4861;
        const lng = parseFloat(document.getElementById('lng').value) || -69.9312;
        initMap(lat, lng, 'map', true);
    });
    
    document.addEventListener('DOMContentLoaded', () => {
        const imagenFileInput = document.getElementById('imagenFile');
        const imagenPreview = document.getElementById('imagenPreview');
        const imagenPreviewImg = document.getElementById('imagenPreviewImg');

        if (imagenFileInput) {
            imagenFileInput.addEventListener('change', e => {
                const file = e.target.files[0];
                if (!file) {
                    imagenPreview.style.display = 'none';
                    return;
                }

                const reader = new FileReader();
                reader.onload = event => {
                    imagenPreviewImg.src = event.target.result;
                    imagenPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }

        if (imagenPreview) {
            imagenPreview.addEventListener('click', () => {
                const url = imagenPreviewImg.src;
                if (url) window.open(url, '_blank');
            });
        }
    });
</script>

</body>
</html>
