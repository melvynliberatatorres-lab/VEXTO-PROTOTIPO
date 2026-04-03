<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'register') {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $tipo_usuario = $_POST['tipo_usuario'];
        $cedula = $_POST['cedula'];
        $genero = $_POST['genero'];
        $rnc = ($tipo_usuario === 'compania') ? $_POST['rnc'] : null;
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $max_propiedades = ($tipo_usuario === 'compania') ? 20 : 3;
        
        // Procesar foto de perfil
        $foto_perfil = null;
        $foto_perfil_tipo = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
            $foto_perfil = file_get_contents($_FILES['foto_perfil']['tmp_name']);
            $foto_perfil_tipo = $_FILES['foto_perfil']['type'];
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO users (nombre, apellido, email, password, tipo_usuario, cedula, rnc, genero, foto_perfil, foto_perfil_tipo, max_propiedades) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $email, $hashed_password, $tipo_usuario, $cedula, $rnc, $genero, $foto_perfil, $foto_perfil_tipo, $max_propiedades]);
            header("Location: index.php?registered=1");
        } catch (PDOException $e) {
            die("Error al registrar: " . $e->getMessage());
        }

    } elseif ($action === 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
            header("Location: dashboard.php");
        } else {
            die("Credenciales incorrectas.");
        }
    }
}
?>
