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

$updated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $genero = $_POST['genero'];
    $telefono = $_POST['telefono'];
    $bio = $_POST['bio'];
    $password = $_POST['password'];
    
    $foto_perfil = $user['foto_perfil'];
    $foto_perfil_tipo = $user['foto_perfil_tipo'];
    
    // Procesar foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
        $foto_perfil = file_get_contents($_FILES['foto_perfil']['tmp_name']);
        $foto_perfil_tipo = $_FILES['foto_perfil']['type'];
    }
    
    try {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, genero = ?, telefono = ?, bio = ?, foto_perfil = ?, foto_perfil_tipo = ?, password = ? WHERE id = ?");
            $stmt->execute([$nombre, $apellido, $genero, $telefono, $bio, $foto_perfil, $foto_perfil_tipo, $hashed_password, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, genero = ?, telefono = ?, bio = ?, foto_perfil = ?, foto_perfil_tipo = ? WHERE id = ?");
            $stmt->execute([$nombre, $apellido, $genero, $telefono, $bio, $foto_perfil, $foto_perfil_tipo, $user_id]);
        }
        
        $_SESSION['nombre'] = $nombre;
        $updated = true;
        
        // Recargar datos del usuario
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        die("Error al actualizar: " . $e->getMessage());
    }
}
?>

<div class="main-container" style="max-width: 900px; margin: 40px auto; flex-direction: column;">
    <div style="width: 100%;">
        <h1 style="margin-bottom: 30px; font-size: 2rem; font-weight: 800;">Mi Cuenta</h1>

        <?php if ($updated): ?>
            <div style="background: #c6f6d5; border: 1px solid #9ae6b4; padding: 15px; border-radius: 8px; color: #22543d; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle"></i>
                <span>Perfil actualizado correctamente.</span>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
            <!-- Foto de Perfil -->
            <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; text-align: center;">
                <h3 style="margin-bottom: 20px;">Foto de Perfil</h3>
                
                <?php if ($user['foto_perfil']): ?>
                    <img id="profilePhotoPreview" src="data:<?php echo htmlspecialchars($user['foto_perfil_tipo']); ?>;base64,<?php echo base64_encode($user['foto_perfil']); ?>" alt="Foto de perfil" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 20px; border: 3px solid var(--accent-color);">
                <?php else: ?>
                    <div id="profilePhotoPreview" style="width: 150px; height: 150px; border-radius: 50%; background: var(--secondary-bg); margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; border: 3px solid var(--border-color);">
                        <i class="fas fa-user" style="font-size: 3rem; color: #ccc;"></i>
                    </div>
                <?php endif; ?>
                
                <form id="photoForm" style="display: none;">
                    <input type="file" id="photoInput" accept="image/*" style="display: none;">
                </form>
                
                <button type="button" onclick="document.getElementById('photoInput').click();" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-camera"></i> Cambiar Foto
                </button>
                
                <div style="margin-top: 20px; padding: 15px; background: var(--secondary-bg); border-radius: 8px;">
                    <div style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">Tipo de Cuenta</div>
                    <div style="font-size: 1.1rem; font-weight: 700;">
                        <?php echo $user['tipo_usuario'] === 'compania' ? 'Empresa / Compañía' : 'Usuario Común'; ?>
                    </div>
                </div>

                <?php if ($user['tipo_usuario'] === 'compania' && $user['rnc']): ?>
                    <div style="margin-top: 15px; padding: 15px; background: var(--secondary-bg); border-radius: 8px;">
                        <div style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">RNC</div>
                        <div style="font-size: 1.1rem; font-weight: 700;"><?php echo htmlspecialchars($user['rnc']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Formulario de Perfil -->
            <form action="settings_new.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" id="fotoPerfilInput" name="foto_perfil">

                <div class="filter-card" style="position: static; margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; font-size: 1.2rem;">Información Personal</h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Apellido</label>
                            <input type="text" name="apellido" value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label>Género</label>
                            <select name="genero" required>
                                <option value="Masculino" <?php echo $user['genero'] === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                                <option value="Femenino" <?php echo $user['genero'] === 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
                                <option value="Otro" <?php echo $user['genero'] === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" name="telefono" value="<?php echo htmlspecialchars($user['telefono'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Biografía</label>
                        <textarea name="bio" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-color); font-family: inherit;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="filter-card" style="position: static; margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; font-size: 1.2rem;">Información de Contacto</h3>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Correo Electrónico</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                        <small style="color: #999; display: block; margin-top: 5px;">El correo electrónico no puede ser modificado.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Cédula</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['cedula']); ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                        <small style="color: #999; display: block; margin-top: 5px;">La cédula no puede ser modificada.</small>
                    </div>
                </div>

                <div class="filter-card" style="position: static; margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; font-size: 1.2rem;">Cambiar Contraseña</h3>

                    <div class="form-group">
                        <label>Nueva Contraseña (Dejar vacío para no cambiar)</label>
                        <input type="password" name="password" placeholder="Ingresa una nueva contraseña o déjalo en blanco">
                        <small style="color: #999; display: block; margin-top: 5px;">Mínimo 8 caracteres recomendado.</small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <button type="submit" class="btn btn-primary" style="padding: 15px; font-size: 1rem;">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="dashboard.php" class="btn btn-outline" style="padding: 15px; font-size: 1rem; text-align: center;">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group label {
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--secondary-bg);
        color: var(--text-color);
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--accent-color);
        background: var(--card-bg);
    }
    
    .btn-outline {
        background: transparent;
        border: 1px solid var(--accent-color);
        color: var(--text-color);
        transition: all 0.3s ease;
    }
    
    .btn-outline:hover {
        background: var(--accent-color);
        color: var(--accent-text);
    }
</style>

<script>
    document.getElementById('photoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('profilePhotoPreview');
                if (preview.tagName === 'IMG') {
                    preview.src = event.target.result;
                } else {
                    preview.innerHTML = '<img src="' + event.target.result + '" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">';
                }
                
                // Crear un FormData para enviar la foto
                const formData = new FormData();
                formData.append('action', 'update_profile');
                formData.append('nombre', document.querySelector('input[name="nombre"]').value);
                formData.append('apellido', document.querySelector('input[name="apellido"]').value);
                formData.append('genero', document.querySelector('select[name="genero"]').value);
                formData.append('telefono', document.querySelector('input[name="telefono"]').value);
                formData.append('bio', document.querySelector('textarea[name="bio"]').value);
                formData.append('password', document.querySelector('input[name="password"]').value);
                formData.append('foto_perfil', file);
                
                // Enviar con AJAX
                fetch('settings_new.php', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (response.ok) {
                        location.reload();
                    }
                });
            };
            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>
