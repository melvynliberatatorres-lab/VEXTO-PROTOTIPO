<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'compania') {
    header("Location: index.php");
    exit();
}
require_once 'db_connect.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Estadísticas generales
$stmt = $pdo->prepare("SELECT COUNT(*) as total_props, SUM(vistas) as total_vistas FROM properties WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Citas pendientes
$stmt = $pdo->prepare("SELECT COUNT(*) as total_citas FROM appointments a JOIN properties p ON a.property_id = p.id WHERE p.user_id = ? AND a.estado = 'pendiente'");
$stmt->execute([$user_id]);
$citas = $stmt->fetch();

// Propiedades más vistas
$stmt = $pdo->prepare("SELECT titulo, vistas, precio FROM properties WHERE user_id = ? ORDER BY vistas DESC LIMIT 5");
$stmt->execute([$user_id]);
$top_props = $stmt->fetchAll();
?>

<div class="main-container" style="flex-direction: column; max-width: 1200px; margin: 40px auto;">
    <h1 style="margin-bottom: 30px; font-size: 2.5rem; font-weight: 900;">Panel de Estadísticas</h1>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px;">
        <div class="filter-card" style="text-align: center; padding: 40px;">
            <h3 style="font-size: 0.9rem; color: #666; text-transform: uppercase; margin-bottom: 10px;">Propiedades Activas</h3>
            <div style="font-size: 3rem; font-weight: 900;"><?php echo $stats['total_props']; ?></div>
        </div>
        <div class="filter-card" style="text-align: center; padding: 40px;">
            <h3 style="font-size: 0.9rem; color: #666; text-transform: uppercase; margin-bottom: 10px;">Vistas Totales</h3>
            <div style="font-size: 3rem; font-weight: 900;"><?php echo number_format($stats['total_vistas'] ?? 0); ?></div>
        </div>
        <div class="filter-card" style="text-align: center; padding: 40px;">
            <h3 style="font-size: 0.9rem; color: #666; text-transform: uppercase; margin-bottom: 10px;">Citas Pendientes</h3>
            <div style="font-size: 3rem; font-weight: 900;"><?php echo $citas['total_citas']; ?></div>
        </div>
    </div>

    <div class="filter-card" style="padding: 40px;">
        <h2 style="margin-bottom: 30px; font-size: 1.8rem; font-weight: 800;">Rendimiento de Publicaciones</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color);">
                    <th style="text-align: left; padding: 15px; font-weight: 800;">Propiedad</th>
                    <th style="text-align: left; padding: 15px; font-weight: 800;">Precio</th>
                    <th style="text-align: left; padding: 15px; font-weight: 800;">Vistas</th>
                    <th style="text-align: left; padding: 15px; font-weight: 800;">Impacto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_props as $prop): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 15px; font-weight: 600;"><?php echo htmlspecialchars($prop['titulo']); ?></td>
                        <td style="padding: 15px;">$<?php echo number_format($prop['precio'], 2); ?></td>
                        <td style="padding: 15px;"><?php echo number_format($prop['vistas']); ?></td>
                        <td style="padding: 15px;">
                            <div style="width: 100%; height: 10px; background: var(--secondary-bg); border-radius: 5px; overflow: hidden;">
                                <div style="width: <?php echo min(100, ($prop['vistas'] / max(1, $stats['total_vistas'])) * 100 * 2); ?>%; height: 100%; background: var(--accent-color);"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
