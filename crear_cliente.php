<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit(); }
require_once 'database.php';

$tipos = $pdo->query("SELECT * FROM tipos_clientes")->fetchAll();

if (isset($_GET['tipo_agregado'])) {
    $mensaje_tipo = '<div class="alert alert-success">Tipo agregado correctamente.</div>';
} elseif (isset($_GET['tipo_error'])) {
    $mensaje_tipo = '<div class="alert alert-danger">Error: Ese tipo ya existe o el nombre está vacío.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $telefono_empresa = $_POST['telefono_empresa'];
    $tipo = $_POST['tipo'];
    $asignado = $_POST['asignado'];
    $compras = $_POST['compras_realizadas'] ?? 0;
    $direccion = $_POST['direccion'];
    $comentario = $_POST['comentario'] ?? '';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO clientes (nombre, apellido, telefono, telefono_empresa, tipo, asignado, compras_realizadas, direccion, comentario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $apellido, $telefono, $telefono_empresa, $tipo, $asignado, $compras, $direccion, $comentario]);
        
        $clienteId = $pdo->lastInsertId();
        
        $usuario = $_SESSION['usuario_nombre'] ?? 'Admin';
        $stmtHist = $pdo->prepare("INSERT INTO historial_clientes (cliente_id, usuario, accion) VALUES (?, ?, ?)");
        $stmtHist->execute([$clienteId, $usuario, 'Contacto creado']);
        
        header("Location: clientes.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CCS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" >
    <link rel="shortcut icon" href="/littlefavicon.ico" type="image/x-icon">
    <title>CRM LogIn</title>
</head>
<body>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: #f6f8fb;
            font-family: "Segoe UI", Arial, sans-serif;
            color: #1e293b;
        }
        /* ==============================
           CONTENIDO
        ============================== */
        .main {
            min-height: 100vh;
            padding: 40px 50px;
        }
        .page-container {
            max-width: 1150px;
            margin: auto;
        }
        /* ==============================
           ENCABEZADO
        ============================== */
        .breadcrumb-custom {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .breadcrumb-custom span {
            color: #64748b;
        }
        .page-title {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            color: #0f172a;
        }
        .page-description {
            margin-top: 7px;
            margin-bottom: 28px;
            color: #64748b;
            font-size: 14px;
        }
        /* ==============================
           CARD
        ============================== */
        .form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .04);
            overflow: hidden;
        }
        .form-content {
            padding: 30px;
        }
        /* ==============================
           SECCIONES
        ============================== */
        .section {
            margin-bottom: 30px;
        }
        .section:last-child {
            margin-bottom: 0;
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 22px;
        }
        .section-icon {
            width: 36px;
            height: 36px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eff6ff;
            color: #2563eb;

            border-radius: 8px;

            font-size: 17px;
        }
        .section-title {
            margin: 0;
            font-size: 15px;
            font-weight: 650;
            color: #1e293b;
        }
        .section-description {
            margin: 2px 0 0;
            font-size: 12px;
            color: #94a3b8;
        }
        /* ==============================
           LABELS
        ============================== */
        .form-label {
            margin-bottom: 7px;

            color: #334155;

            font-size: 13px;
            font-weight: 600;
        }
        .required {
            color: #ef4444;
        }
        /* ==============================
           INPUTS
        ============================== */
        .form-control,
        .form-select {
            height: 44px;

            border: 1px solid #dbe2ea;
            border-radius: 8px;

            font-size: 14px;

            color: #1e293b;

            transition: .2s;
        }
        textarea.form-control {
            height: auto;
            min-height: 100px;
            resize: vertical;
        }
        .form-control::placeholder {
            color: #a8b1bd;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow:
                0 0 0 3px rgba(37, 99, 235, .08);
        }
        /* ==============================
           TIPO + BOTÓN
        ============================== */
        .input-group .form-select {
            border-radius: 8px 0 0 8px;
        }
        .input-group .btn {
            width: 46px;
            border-radius: 0 8px 8px 0;
        }
        /* ==============================
           COMENTARIO
        ============================== */
        .comment-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;

            border-radius: 10px;

            padding: 20px;
        }
        /* ==============================
           FOOTER BOTONES
        ============================== */
        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;

            padding: 20px 30px;

            border-top: 1px solid #e2e8f0;

            background: #fafbfc;
        }
        .btn-cancel {
            height: 40px;

            padding: 0 18px;

            border: 1px solid #dbe2ea;
            border-radius: 7px;

            background: white;
            color: #64748b;

            font-size: 13px;
            font-weight: 600;
        }
        .btn-cancel:hover {
            background: #f1f5f9;
        }
        .btn-save {
            height: 40px;

            padding: 0 20px;

            border: none;
            border-radius: 7px;

            background: #2563eb;
            color: white;

            font-size: 13px;
            font-weight: 600;

            transition: .2s;
        }
        .btn-save:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        /* ==============================
           ALERTAS
        ============================== */
        .alert {
            border-radius: 8px;
            font-size: 13px;
        }
        /* ==============================
           MODAL TIPO
        ============================== */
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,.15);
        }
        .modal-header {
            border-bottom: 1px solid #e5e7eb;
        }
        .modal-title {
            font-size: 16px;
            font-weight: 600;
        }
        /* ==============================
           RESPONSIVE
        ============================== */
        @media (max-width: 768px) {
            .main {
                padding: 25px 18px;
            }
            .form-content {
                padding: 22px;
            }
            .form-footer {
                padding: 18px 22px;
            }
        }
    </style>

<main class="main">
    <div class="page-container">
        <!-- ENCABEZADO -->
        <div class="breadcrumb-custom">
            Clientes / <span>Nuevo cliente</span>
        </div>
        <h1 class="page-title">
            Nuevo cliente
        </h1>
        <p class="page-description">
            Registra la información del cliente para incorporarlo al sistema.
        </p>

        <!-- FORMULARIO -->
        <div class="form-card">
            <div class="form-content">
                <?php echo $mensaje_tipo ?? ''; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" id="formCliente ">
                    <!-- ==========================
                         INFORMACIÓN
                    =========================== -->
                    <div class="section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <h2 class="section-title">
                                    Información del cliente
                                </h2>
                                <p class="section-description">
                                    Datos principales del contacto.
                                </p>
                            </div>
                        </div>
                        <div class="row g-4">
                            <!-- NOMBRE -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    Nombre
                                </label>
                                <input
                                    type="text"
                                    name="nombre"
                                    class="form-control"
                                    placeholder="Ej. Jhosue"
                                    required
                                >
                            </div>
                            <!-- APELLIDO -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    Apellido <span class="required">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="apellido"
                                    class="form-control"
                                    placeholder="Ej. Mayorga"
                                    required
                                >
                            </div>

                            <!-- MÓVIL -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    Móvil
                                </label>
                                <input
                                    type="text"
                                    name="telefono"
                                    class="form-control"
                                    placeholder="Ej. 970 408 931"
                                >
                            </div>

                            <!-- TELÉFONO EMPRESA -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    Teléfono empresa
                                </label>
                                <input
                                    type="text"
                                    name="telefono_empresa"
                                    class="form-control"
                                    placeholder="Número de empresa"
                                >
                            </div>

                            <!-- TIPO -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    Tipo <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <select
                                        name="tipo"
                                        class="form-select"
                                        required
                                    >
                                        <?php foreach ($tipos as $t): ?>
                                            <option value="<?php echo htmlspecialchars($t['nombre']); ?>">
                                                <?php echo htmlspecialchars($t['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalTipo"
                                    >
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- ASIGNADO -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    Asignado a
                                </label>
                                <select
                                    name="asignado"
                                    class="form-select"
                                >
                                    <option>Alonso Zapata Olmos</option>
                                    <option>Diana Moran Carranza</option>
                                    <option>Oscar Silva</option>
                                </select>
                            </div>

                            <!-- DIRECCIÓN -->
                            <div class="col-md-8">
                                <label class="form-label">
                                    Dirección de facturación
                                </label>
                                <textarea
                                    name="direccion"
                                    class="form-control"
                                    placeholder="Ingresa la dirección del cliente..."
                                ></textarea>
                            </div>

                            <!-- COMPRAS -->
                            <div class="col-md-4">
                                <label class="form-label">
                                    Compras realizadas
                                </label>
                                <input
                                    type="number"
                                    name="compras_realizadas"
                                    class="form-control"
                                    value="0"
                                    min="0"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES -->
               
            </div>

            <div class="form-footer">
                <a
                    href="clientes.php"
                    class="btn btn-cancel"
                >
                    Cancelar
                </a>
                <button
                    form="formCliente"
                    type="submit"
                    class="btn-save"
                >
                    <i class="bi bi-check-lg me-1"></i>
                    Guardar cliente
                </button>
                </div>
             </form>
        </div>
    </div>
</main>

<!-- ==========================
     MODAL TIPO
=========================== -->
<div
    class="modal fade"
    id="modalTipo"
    tabindex="-1"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form
                action="agregar_tipo.php"
                method="POST"
            >
                <div class="modal-header">
                    <h5 class="modal-title">
                        Agregar nuevo tipo
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">
                        Nombre del tipo
                    </label>
                    <input
                        type="text"
                        name="nuevo_tipo"
                        class="form-control"
                        placeholder="Ej. Cliente VIP"
                        required
                    >
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Agregar tipo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>