<?php
session_start();
require_once 'database.php';

// Si ya está logueado, ir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error = 'Complete todos los campos';
    } else {
        // Buscar usuario (MD5 para coincidir con los datos de prueba)
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND password = MD5(?)");
        $stmt->execute([$email, $password]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = '❌ Email o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>🔐 Login CRM</h1>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="campo">
                    <label>📧 Email</label>
                    <input type="email" name="email" placeholder="admin@crm.com" required>
                </div>
                
                <div class="campo">
                    <label>🔑 Contraseña</label>
                    <input type="password" name="password" placeholder="admin123" required>
                </div>
                
                <button type="submit">Ingresar</button>
            </form>
            
            <div class="info">
                <strong>Usuarios de prueba:</strong><br>
                admin@crm.com / admin123<br>
                juan@crm.com / juan123
            </div>
        </div>
    </div>
</body>
</html>