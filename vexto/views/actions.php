<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit();
}
require_once dirname(__DIR__) . '/config/db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'toggle_favorite') {
        $property_id = (int)$_POST['property_id'];
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$user_id, $property_id]);
        $fav = $stmt->fetch();

        if ($fav) {
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
            $stmt->execute([$fav['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $property_id]);
        }
        header("Location: property_details.php?id=$property_id");

    } elseif ($action === 'schedule_appointment') {
        $property_id = (int)$_POST['property_id'];
        $fecha = $_POST['fecha'];
        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, property_id, fecha_cita) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $property_id, $fecha]);
        header("Location: property_details.php?id=$property_id&appointment=success");

    } elseif ($action === 'report') {
        $property_id = (int)$_POST['property_id'];
        $motivo = $_POST['motivo'];
        $stmt = $pdo->prepare("INSERT INTO reports (user_id, property_id, motivo) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $property_id, $motivo]);
        header("Location: property_details.php?id=$property_id&report=success");
    }
}
?>
