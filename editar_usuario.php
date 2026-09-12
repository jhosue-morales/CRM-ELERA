<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
if ($_SESSION['usuario_rol'] !== 'admin') { header('Location: dashboard.php?error=sin_permiso'); exit(); }
require_once 'database.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    
    // Si escribió una contraseña nueva, la actualizamos. Si no, dejamos la misma.
    if (!empty($_POST['password'])) {
        $password = MD5($_POST['password']);
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=? WHERE id=?");
        $stmt->execute([$nombre, $email, $password, $rol, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?");
        $stmt->execute([$nombre, $email, $rol, $id]);
    }
    
    header("Location: usuarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Editar Usuario - CRM</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f6f8fb; font-family: "Segoe UI", Arial, sans-serif; color: #1e293b; }
        .main { min-height: 100vh; padding: 40px 50px; }
        .page-container { max-width: 1150px; margin: auto; }
        .breadcrumb-custom { font-size: 13px; color: #94a3b8; margin-bottom: 6px; }
        .breadcrumb-custom span { color: #64748b; }
        .page-title { margin: 0; font-size: 26px; font-weight: 700; color: #0f172a; }
        .page-description { margin-top: 7px; margin-bottom: 28px; color: #64748b; font-size: 14px; }
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 3px 12px rgba(15, 23, 42, .04); overflow: hidden; }
        .form-content { padding: 30px; }
        .section { margin-bottom: 30px; }
        .section-header { display: flex; align-items: center; gap: 11px; margin-bottom: 22px; }
        .section-icon { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: #eff6ff; color: #2563eb; border-radius: 8px; font-size: 17px; }
        .section-title { margin: 0; font-size: 15px; font-weight: 650; color: #1e293b; }
        .section-description { margin: 2px 0 0; font-size: 12px; color: #94a3b8; }
        .form-label { margin-bottom: 7px; color: #334155; font-size: 13px; font-weight: 600; }
        .form-control, .form-select { height: 44px; border: 1px solid #dbe2ea; border-radius: 8px; font-size: 14px; color: #1e293b; transition: .2s; }
        .form-control:focus, .form-select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .08); }
        .form-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 20px 30px; border-top: 1px solid #e2e8f0; background: #fafbfc; }
        .btn-cancel { height: 40px; padding: 0 18px; border: 1px solid #dbe2ea; border-radius: 7px; background: white; color: #64748b; font-size: 13px; font-weight: 600; }
        .btn-cancel:hover { background: #f1f5f9; }
        .btn-save { height: 40px; padding: 0 20px; border: none; border-radius: 7px; background: #2563eb; color: white; font-size: 13px; font-weight: 600; transition: .2s; }
        .btn-save:hover { background: #1d4ed8; transform: translateY(-1px); }
    </style>
</head>
<body>
<main class="main">
    <div class="page-container">
        <div class="breadcrumb-custom">Usuarios / <span>Editar usuario</span></div>
        <h1 class="page-title">Editar Usuario</h1>
        <p class="page-description">Modifica la información del usuario.</p>

        <div class="form-card">
            <div class="form-content">
                <form method="POST" id="formUsuario">
                    <div class="section">
                        <div class="section-header">
                            <div class="section-icon"><i class="bi bi-person-badge"></i></div>
                            <div>
                                <h2 class="section-title">Información del Usuario</h2>
                                <p class="section-description">Modifica los datos de acceso.</p>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" value="<?php echo $usuario['nombre']; ?>" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="<?php echo $usuario['email']; ?>" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña (Dejar en blanco para no cambiar)</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rol</label>
                                <select name="rol" class="form-select" required>
                                    <option value="vendedor" <?php if($usuario['rol'] == 'vendedor') echo 'selected'; ?>>Vendedor</option>
                                    <option value="admin" <?php if($usuario['rol'] == 'admin') echo 'selected'; ?>>Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <a href="usuarios.php" class="btn btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-save"><i class="bi bi-check-lg me-1"></i> Actualizar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>