<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit();
}
require_once dirname(__DIR__) . '/config/db.php';
include dirname(__DIR__) . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Obtener datos del vendedor
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$seller = $stmt->fetch();

if (!$seller) die("Vendedor no encontrado.");

// Obtener propiedades del vendedor
$stmt = $pdo->prepare("SELECT * FROM properties WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$properties = $stmt->fetchAll();

// Obtener reseñas
$stmt = $pdo->prepare("SELECT r.*, u.nombre, u.apellido FROM reviews r JOIN users u ON r.reviewer_id = u.id WHERE r.seller_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();
?>

<div class="main-container" style="flex-direction: column; max-width: 1200px; margin: 40px auto;">
    <div class="filter-card" style="width: 100%; position: static; display: flex; align-items: center; gap: 40px; padding: 50px; margin-bottom: 40px;">
        <div class="seller-avatar-mini" style="width: 150px; height: 150px;"></div>
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <h1 style="font-size: 2.5rem; font-weight: 900;"><?php echo htmlspecialchars($seller['nombre'] . ' ' . $seller['apellido']); ?></h1>
                <?php if ($seller['tipo_usuario'] == 'compania'): ?>
                    <span style="background: #000; color: #fff; padding: 5px 15px; border-radius: 6px; font-size: 0.8rem; font-weight: 800;">COMPAÑÍA VERIFICADA</span>
                <?php endif; ?>
            </div>
            <div style="font-size: 1.2rem; margin-bottom: 15px;">
                <i class="fas fa-star"></i> <?php echo number_format($seller['rating'], 1); ?> 
                <span style="font-size: 0.9rem; color: #666;">(<?php echo $seller['total_reviews']; ?> reseñas)</span>
            </div>
            <p style="font-size: 1.1rem; color: #666; max-width: 800px;"><?php echo nl2br(htmlspecialchars($seller['bio'] ?: 'Sin biografía disponible.')); ?></p>
        </div>
    </div>

    <div style="display: flex; gap: 30px; border-bottom: 2px solid var(--border-color); margin-bottom: 30px;">
        <div class="tab active" style="padding: 15px 30px; cursor: pointer; font-weight: 800; border-bottom: 4px solid var(--accent-color);">Proyectos Activos (<?php echo count($properties); ?>)</div>
        <div class="tab" style="padding: 15px 30px; cursor: pointer; font-weight: 800; color: #666;">Reseñas de Clientes (<?php echo count($reviews); ?>)</div>
    </div>

    <div class="content-grid">
        <?php foreach ($properties as $prop): ?>
            <?php
                $imageSrc = getPropertyImageUrl($prop['imagen_url']);
                if (!empty($prop['imagen'])) {
                    $imageSrc = 'data:' . htmlspecialchars($prop['imagen_tipo']) . ';base64,' . base64_encode($prop['imagen']);
                }
            ?>
            <div class="property-card" onclick="location.href='property_details.php?id=<?php echo $prop['id']; ?>'">
                <div class="property-img-container">
                    <span class="op-badge"><?php echo $prop['tipo_operacion']; ?></span>
                    <img src="<?php echo htmlspecialchars($imageSrc); ?>" class="property-img" alt="Propiedad">
                </div>
                <div class="property-info">
                    <div class="property-price">$<?php echo number_format($prop['precio'], 2); ?></div>
                    <div class="property-title"><?php echo htmlspecialchars($prop['titulo']); ?></div>
                    <div class="property-meta">
                        <span><i class="fas fa-bed"></i> <?php echo $prop['habitaciones']; ?></span>
                        <span><i class="fas fa-bath"></i> <?php echo $prop['banos']; ?></span>
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($prop['ubicacion']); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
