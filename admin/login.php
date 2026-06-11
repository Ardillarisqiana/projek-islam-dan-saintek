<?php
session_start();
// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin LAZISNU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1b5e20 0%, #0f281f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Inter', sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: #1b5e20;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header i {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .login-body {
            padding: 40px;
        }
        .btn-login {
            background: #1b5e20;
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            background: #0f4814;
        }
        .alert-custom {
            border-radius: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="login-card">
                <div class="login-header">
                    <i class="fas fa-mosque"></i>
                    <h3 class="mb-0">LAZISNU</h3>
                    <p class="mb-0 opacity-75">Admin Panel</p>
                </div>
                <div class="login-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-custom">
                            <i class="fas fa-exclamation-circle"></i> 
                            <?php 
                                if ($_GET['error'] == 'invalid') echo 'Username atau password salah!';
                                elseif ($_GET['error'] == 'login') echo 'Silakan login terlebih dahulu.';
                                else echo 'Terjadi kesalahan, silakan coba lagi.';
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="proses_login.php">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user"></i> Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                        <button type="submit" class="btn btn-login text-white w-100">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                    <hr class="my-4">
                    <div class="text-center text-muted small">
                        <i class="fas fa-info-circle"></i> Default: username <strong>admin</strong> | password <strong>admin123</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>