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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CCS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" >
    <title>CRM LogIn</title>
</head>
<body>
    <style>
        body { 
            min-height: 100vh; 
            margin: 0; 
            font-family: "Inter", "Segoe UI", sans-serif; 
            background: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        :root {
            --text-dark: #0f172a;
            --primary: #11468F;
            --primary-dark: #041562;
            --text-muted: #EEEEEE;
        }

        .left-panel { 
            position: relative; 
            min-height: 100vh; 
            overflow: hidden; 
            background: linear-gradient(#11468F, #041562); 
            color: white; 
        }

        .login-container { 
            min-height: 100vh; 
        }

        .left-logo { 
            width: 5em; 
            height: 2.5em; 
            border-radius: 14px; 
            display: flex; 
            align-items: center; 
            font-size: 24px; font-weight: 700; 
            backdrop-filter: blur(10px); 
        }

        /* PANEL LOGIN */ 
        .login-panel { 
            min-height: 100vh; 
            background: #EEEEEE;
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .login-box {
            width: 100%; 
            max-width: 420px; 
            padding: 30px; 
        } 
        .login-title { 
            font-weight: 700; 
            font-size: 1.9rem; 
            color: var(--text-dark); 
            letter-spacing: -0.5px; 
        } 
        .form-label { 
            font-weight: 600; 
            font-size: 0.9rem; 
            color: #334155; 
        } 
        .input-group-custom { 
            position: relative; 
        } 
        .input-icon { 
            position: absolute; 
            left: 15px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #94a3b8; 
            z-index: 5; 
        } 
        .form-control-custom { 
            height: 52px; 
            padding-left: 45px; 
            padding-right: 45px; 
            border: 1px solid #e2e8f0; 
            border-radius: 11px; 
            background: #f8fafc; 
            transition: all 0.2s ease; 
        } 
        .form-control-custom:focus { 
            background: white; 
            border-color: var(--primary); 
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); 
        } 
        
        .forgot-link { 
            color: var(--primary); 
            text-decoration: none; 
            font-size: 0.9rem; 
            font-weight: 600; 
        } 
        .forgot-link:hover { 
            color: var(--primary-dark); 
        } 
        .btn-login { 
            height: 52px; 
            border: none; 
            border-radius: 11px; 
            background: var(--primary); 
            color: white; 
            font-weight: 600; 
            font-size: 0.95rem; 
            transition: all 0.2s ease; 
        } 
        .btn-login:hover { 
            background: var(--primary-dark); 
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2); 
            color: #EEEEEE;
        } 
        .security-info { 
            color: #94a3b8; 
            font-size: 0.8rem; 
        } 
        .security-info i { 
            color: #22c55e; 
        } 
        .footer-text { 
            color: #94a3b8; 
            font-size: 0.78rem; 
        }

        /* RESPONSIVE */ 
        @media (max-width: 991px) { 
            .brand-panel { 
                display: none; 
            } 
            .mobile-logo { 
                display: flex; 
                width: 48px; 
                height: 48px; 
                align-items: center; 
                justify-content: center; 
                border-radius: 12px; 
                background: var(--primary); 
                color: white; 
                font-weight: 700; 
                font-size: 20px; 
                margin-bottom: 25px; 
            } 
            .login-box { 
                max-width: 450px; 
            } 
        } 
        @media (max-width: 576px) { 
            .login-panel { 
                padding: 20px; 
            } 
            .login-box { 
                padding: 15px; 
            } 
            .login-title { 
                font-size: 1.7rem; 
            } 
        }
    </style>
    
    
    <div class="container-fluid p-0"> 
        <div class="row g-0 login-container"> 
            <!-- ========================= PANEL DE MARCA ========================== --> 
            <div class="col-lg-6 left-panel"> 
                <div class="circle circle-1"></div> 
                <div class="circle circle-2"></div> 
                <div class="d-flex align-items-center h-100 p-5"> 
                    <div class="left-content"> 
                    <!-- LOGO --> 
                        <div class="left-logo mb-4">
                            <img src="/Screenshot_1.png" alt="" srcset="" width="100%">
                        </div> 
                        <h1 class="left-title mb-4"> 
                            Gestiona tu negocio. <br> <span style="color:#60a5fa;"> Más simple. </span> 
                        </h1> 
                        <p class="left-description mb-5"> 
                            Centraliza clientes, oportunidades, cotizaciones y ventas desde una sola plataforma. 
                        </p>
                        <!-- FEATURES --> 
                        <div class="feature"> 
                            <div class="feature-icon"> 
                                <i class="bi bi-people"></i>
                                <span>Gestión centralizada de clientes</span> 
                            </div> 
                        </div> 
                        <div class="feature"> 
                            <div class="feature-icon"> 
                                <i class="bi bi-graph-up-arrow"></i> 
                                <span>Seguimiento de oportunidades y ventas</span> 
                            </div> 
                        </div> 
                        <div class="feature"> 
                            <div class="feature-icon"> 
                                <i class="bi bi-file-earmark-text"></i> 
                                <span>Cotizaciones organizadas en un solo lugar</span> 
                            </div> 
                        </div> 
                    </div> 
                </div> 
            </div> 
            <!-- ========================= PANEL LOGIN ========================== --> 
            <div class="col-lg-6 login-panel"> 
                <div class="login-box"> 
                    <!-- LOGO MOBILE --> 
                    <div class="left-logo">
                        <img src="/" alt="" srcset="" width="100%">
                    </div> 
                    <div class="mb-4"> 
                        <h2 class="login-title mb-2"> Bienvenido </h2> 
                        <p class="login-subtitle mb-0"> Ingresa a tu cuenta para continuar. </p> 
                    </div> 
                    <?php if ($error): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
            
                    <!-- FORMULARIO -->
                    <form method="POST"> 
                        <!-- USUARIO --> 
                        <div class="mb-3"> 
                            <label class="form-label"> Correo electrónico </label> 
                            <div class="input-group-custom"> 
                                <i class="bi bi-envelope input-icon"></i> 
                                <input type="email" name="email" class="form-control form-control-custom" placeholder="usuario@empresa.com" required > 
                            </div> 
                        </div> 
                        <!-- PASSWORD --> 
                        <div class="mb-3"> 
                            <div class="d-flex justify-content-between mb-2"> 
                                <label class="form-label mb-0"> Contraseña </label> 
                                <a href="#" class="forgot-link"> ¿Olvidaste tu contraseña? </a> 
                            </div> 
                            <div class="input-group-custom"> 
                                <i class="bi bi-lock input-icon"></i> 
                                <input type="password" name="password" class="form-control form-control-custom" placeholder="Ingresa tu contraseña" required > 
                                </button> 
                            </div> 
                        </div> 
                        <!-- LOGIN -->
                        <button type="submit" class="btn btn-login w-100" > Iniciar sesión <i class="bi bi-arrow-right ms-2"></i></button> 
                    </form> 
                    <!-- SEGURIDAD --> 
                    <div class="text-center mt-4 security-info"> 
                        <i class="bi bi-shield-check me-1"></i> Tus datos están protegidos y cifrados. 
                    </div> 
                    <!-- FOOTER --> 
                    <div class="text-center mt-5 footer-text"> © 2026 Yosan SAC · Todos los derechos reservados </div> 
                </div> 
            </div> 
        </div> 
    </div>
</body>
</html>