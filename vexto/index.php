<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VEXTO | Inmobiliaria Profesional</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #ffffff; --text: #000000; --border: #e0e0e0; --accent: #000000; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center; overflow: hidden; }
        
        #splash { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #000; display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .logo-anim { font-size: 6rem; font-weight: 900; letter-spacing: 20px; opacity: 0; color: #fff; text-transform: uppercase; }
        
        .auth-card { width: 100%; max-width: 450px; padding: 40px; background: #fff; border: 1px solid var(--border); border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); opacity: 0; transform: translateY(30px); }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header h1 { font-size: 2rem; font-weight: 900; margin-bottom: 8px; }
        .auth-header p { color: #666; font-size: 0.9rem; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; }
        input, select { width: 100%; padding: 14px; background: #f9f9f9; border: 1px solid var(--border); border-radius: 12px; color: #000; font-size: 1rem; outline: none; transition: all 0.3s; }
        input:focus { border-color: #000; background: #fff; }
        
        .btn-primary { width: 100%; padding: 16px; background: #000; color: #fff; border: none; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: transform 0.2s; }
        .btn-primary:hover { transform: scale(1.02); }
        
        .toggle-auth { text-align: center; margin-top: 25px; color: #666; font-size: 0.9rem; }
        .toggle-auth span { color: #000; cursor: pointer; font-weight: 700; text-decoration: underline; }
        .hidden { display: none; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>

    <div id="splash">
        <div class="logo-anim">VEXTO</div>
    </div>

    <div class="auth-card" id="auth-container">
        <!-- Login -->
        <div id="login-section">
            <div class="auth-header">
                <h1>VEXTO</h1>
                <p>Ingresa a tu cuenta inmobiliaria</p>
            </div>
            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary">Iniciar Sesión</button>
            </form>
            <div class="toggle-auth">
                ¿No tienes cuenta? <span onclick="switchForm('register')">Regístrate aquí</span>
            </div>
        </div>

        <!-- Register -->
        <div id="register-section" class="hidden">
            <div class="auth-header">
                <h1>Únete a VEXTO</h1>
                <p>Crea tu perfil de usuario o compañía</p>
            </div>
            <form action="auth.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="register">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido</label>
                        <input type="text" name="apellido" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Género</label>
                        <select name="genero" required>
                            <option value="">Selecciona tu género</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cédula</label>
                        <input type="text" name="cedula" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Tipo de Cuenta</label>
                        <select name="tipo_usuario" id="tipoUsuario" required onchange="toggleRNC()">
                            <option value="usuario">Usuario Común</option>
                            <option value="compania">Compañía / Empresa</option>
                        </select>
                    </div>
                    <div class="form-group" id="rncField" style="display: none;">
                        <label>RNC (Registro Nacional de Contribuyente)</label>
                        <input type="text" name="rnc">
                    </div>
                </div>
                <div class="form-group">
                    <label>Foto de Perfil</label>
                    <input type="file" name="foto_perfil" accept="image/*" required>
                    <small style="color: #999; margin-top: 5px; display: block;">Sube una foto de perfil (JPG, PNG, etc.)</small>
                </div>
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary">Crear Cuenta</button>
            </form>
            <div class="toggle-auth">
                ¿Ya tienes cuenta? <span onclick="switchForm('login')">Inicia sesión</span>
            </div>
        </div>
    </div>

    <script>
        window.onload = () => {
            const tl = anime.timeline({ easing: 'easeOutExpo' });
            tl.add({ targets: '.logo-anim', opacity: [0, 1], scale: [0.5, 1], letterSpacing: ['40px', '20px'], duration: 2000, delay: 500 })
              .add({ targets: '#splash', opacity: 0, duration: 1000, delay: 800, complete: () => {
                    document.getElementById('splash').style.display = 'none';
                    anime({ targets: '#auth-container', opacity: [0, 1], translateY: [50, 0], duration: 1200, easing: 'easeOutQuart' });
                }
            });
        };

        function switchForm(type) {
            const login = document.getElementById('login-section');
            const register = document.getElementById('register-section');
            if (type === 'register') {
                login.classList.add('hidden');
                register.classList.remove('hidden');
                anime({ targets: '#register-section', opacity: [0, 1], duration: 400, easing: 'easeOutQuad' });
            } else {
                register.classList.add('hidden');
                login.classList.remove('hidden');
                anime({ targets: '#login-section', opacity: [0, 1], duration: 400, easing: 'easeOutQuad' });
            }
        }
        
        function toggleRNC() {
            const tipoUsuario = document.getElementById('tipoUsuario').value;
            const rncField = document.getElementById('rncField');
            if (tipoUsuario === 'compania') {
                rncField.style.display = 'block';
                anime({ targets: '#rncField', opacity: [0, 1], duration: 300, easing: 'easeOutQuad' });
            } else {
                rncField.style.display = 'none';
            }
        }
    </script>
</body>
</html>
