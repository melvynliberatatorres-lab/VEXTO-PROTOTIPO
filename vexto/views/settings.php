<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit();
}
require_once dirname(__DIR__) . '/config/db.php';
include dirname(__DIR__) . '/includes/header.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $telefono = $_POST['telefono'];
        $bio = $_POST['bio'];
        $password = $_POST['password'];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, telefono = ?, bio = ?, password = ? WHERE id = ?");
            $stmt->execute([$nombre, $apellido, $telefono, $bio, $hashed_password, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, telefono = ?, bio = ? WHERE id = ?");
            $stmt->execute([$nombre, $apellido, $telefono, $bio, $user_id]);
        }
        $_SESSION['nombre'] = $nombre;
        header("Location: settings.php?updated=1");
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();
?>

<div class="main-container" style="flex-direction: column; max-width: 800px; margin: 40px auto;">
    <h1 style="margin-bottom: 30px; font-size: 2.5rem; font-weight: 900;">Configuración de Cuenta</h1>
    
    <div class="filter-card" style="padding: 40px;">
        <form action="settings.php" method="POST">
            <input type="hidden" name="update_profile" value="1">
            
            <div class="filter-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($user_data['nombre']); ?>" required>
                </div>
                <div>
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($user_data['apellido']); ?>" required>
                </div>
            </div>

            <div class="filter-group">
                <label>Teléfono de Contacto</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($user_data['telefono']); ?>" required>
            </div>

            <div class="filter-group">
                <label>Biografía / Descripción del Vendedor</label>
                <textarea name="bio" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-color);"><?php echo htmlspecialchars($user_data['bio']); ?></textarea>
            </div>

            <div class="filter-group">
                <label>Nueva Contraseña (dejar en blanco para no cambiar)</label>
                <input type="password" name="password" placeholder="********">
            </div>

            <div style="background: var(--secondary-bg); padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid var(--border-color);">
                <h3 style="font-size: 0.9rem; text-transform: uppercase; margin-bottom: 10px;">Información de Cuenta</h3>
                <p>Tipo de Usuario: <strong><?php echo strtoupper($user_data['tipo_usuario']); ?></strong></p>
                <p>Cédula / RNC: <strong><?php echo htmlspecialchars($user_data['cedula']); ?></strong></p>
                <p>Límite de Publicaciones: <strong><?php echo $user_data['max_propiedades']; ?></strong></p>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem;">Guardar Cambios</button>
        </form>
    </div>
</div>

</body>
</html>
