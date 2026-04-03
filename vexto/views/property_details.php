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

// Incrementar vistas
$stmt = $pdo->prepare("UPDATE properties SET vistas = vistas + 1 WHERE id = ?");
$stmt->execute([$id]);

// Obtener detalles
$stmt = $pdo->prepare("SELECT p.*, u.nombre, u.apellido, u.rating, u.total_reviews, u.foto_perfil, u.tipo_usuario, u.bio 
                      FROM properties p 
                      JOIN users u ON p.user_id = u.id 
                      WHERE p.id = ?");
$stmt->execute([$id]);
$prop = $stmt->fetch();

if (!$prop) die("Propiedad no encontrada.");

// Verificar si es favorito
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
$stmt->execute([$user_id, $id]);
$is_fav = $stmt->fetch();
?>

<div class="main-container" style="flex-direction: column; max-width: 1200px; margin: 40px auto;">
    <?php
        $imageSrc = getPropertyImageUrl($prop['imagen_url']);
        if (!empty($prop['imagen'])) {
            $imageSrc = 'data:' . htmlspecialchars($prop['imagen_tipo']) . ';base64,' . base64_encode($prop['imagen']);
        }
    ?>
    <div class="property-img-container" style="height: 500px; border-radius: 12px; margin-bottom: 30px;">
        <span class="op-badge" style="font-size: 1rem; padding: 8px 20px;"><?php echo $prop['tipo_operacion']; ?></span>
        <img src="<?php echo htmlspecialchars($imageSrc); ?>" class="property-img" alt="Propiedad">
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
        <div class="info-section">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div>
                    <h1 style="font-size: 2.5rem; font-weight: 900;"><?php echo htmlspecialchars($prop['titulo']); ?></h1>
                    <p style="font-size: 1.2rem; color: #666;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($prop['ubicacion']); ?></p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 2.5rem; font-weight: 900;">$<?php echo number_format($prop['precio'], 2); ?></div>
                    <p style="font-size: 0.9rem; color: #666;">Precio de <?php echo $prop['tipo_operacion']; ?></p>
                </div>
            </div>

            <div style="display: flex; gap: 30px; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 20px 0; margin-bottom: 30px;">
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800;"><?php echo $prop['habitaciones']; ?></div>
                    <div style="font-size: 0.8rem; color: #666; text-transform: uppercase;">Habitaciones</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800;"><?php echo $prop['banos']; ?></div>
                    <div style="font-size: 0.8rem; color: #666; text-transform: uppercase;">Baños</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800;"><?php echo $prop['area_m2'] ?: 'N/A'; ?></div>
                    <div style="font-size: 0.8rem; color: #666; text-transform: uppercase;">Área m²</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800;"><?php echo ucfirst($prop['tipo_propiedad']); ?></div>
                    <div style="font-size: 0.8rem; color: #666; text-transform: uppercase;">Tipo</div>
                </div>
            </div>

            <div style="margin-bottom: 40px;">
                <h3 style="margin-bottom: 15px; font-size: 1.5rem; font-weight: 800;">Descripción del Proyecto</h3>
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-color);"><?php echo nl2br(htmlspecialchars($prop['descripcion'])); ?></p>
            </div>

            <div style="margin-bottom: 40px;">
                <h3 style="margin-bottom: 15px; font-size: 1.5rem; font-weight: 800;">Ubicación Exacta</h3>
                <div id="map" style="height: 400px;"></div>
            </div>
        </div>

        <aside class="actions-sidebar">
            <div class="filter-card" style="position: sticky; top: 100px;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; cursor: pointer;" onclick="location.href='profile.php?id=<?php echo $prop['user_id']; ?>'">
                    <div class="seller-avatar-mini" style="width: 60px; height: 60px;"></div>
                    <div>
                        <div style="font-weight: 800; font-size: 1.1rem;"><?php echo htmlspecialchars($prop['nombre'] . ' ' . $prop['apellido']); ?></div>
                        <div style="font-size: 0.85rem; color: #666;">
                            <?php if ($prop['tipo_usuario'] == 'compania'): ?>
                                <span style="background: #000; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; margin-right: 5px;">COMPAÑÍA</span>
                            <?php endif; ?>
                            <i class="fas fa-star"></i> <?php echo number_format($prop['rating'], 1); ?> (<?php echo $prop['total_reviews']; ?> reseñas)
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary" style="width: 100%; padding: 15px; margin-bottom: 15px;" onclick="openModal()">Agendar Cita</button>
                
                <form action="actions.php" method="POST">
                    <input type="hidden" name="action" value="toggle_favorite">
                    <input type="hidden" name="property_id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn btn-outline" style="width: 100%; padding: 15px; margin-bottom: 15px;">
                        <i class="<?php echo $is_fav ? 'fas' : 'far'; ?> fa-heart"></i> 
                        <?php echo $is_fav ? 'Guardado en Favoritos' : 'Guardar en Favoritos'; ?>
                    </button>
                </form>

                <button class="btn" style="width: 100%; color: #ff4444; font-size: 0.8rem; background: transparent; border: 1px solid #ff4444;" onclick="reportPost()">Reportar Publicación</button>
            </div>
        </aside>
    </div>
</div>

<!-- Modal Cita -->
<div id="appointmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center;">
    <div class="filter-card" style="width: 400px; position: static;">
        <h2 style="margin-bottom: 20px;">Agendar Cita</h2>
        <form action="actions.php" method="POST">
            <input type="hidden" name="action" value="schedule_appointment">
            <input type="hidden" name="property_id" value="<?php echo $id; ?>">
            <div class="filter-group">
                <label>Selecciona Fecha y Hora:</label>
                <input type="datetime-local" name="fecha" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 10px;">Confirmar Cita</button>
            <button type="button" class="btn btn-outline" style="width: 100%;" onclick="closeModal()">Cancelar</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initMap(<?php echo $prop['latitud'] ?: 18.4861; ?>, <?php echo $prop['longitud'] ?: -69.9312; ?>, 'map', false);
    });

    function openModal() { document.getElementById('appointmentModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('appointmentModal').style.display = 'none'; }
    function reportPost() {
        const motivo = prompt("¿Por qué deseas reportar esta publicación?");
        if (motivo) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'actions.php';
            form.innerHTML = `
                <input type="hidden" name="action" value="report">
                <input type="hidden" name="property_id" value="<?php echo $id; ?>">
                <input type="hidden" name="motivo" value="${motivo}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

</body>
</html>
