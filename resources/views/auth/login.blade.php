<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            padding: 48px 40px;
            border: 1px solid #e8edf2;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-circle {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .logo-circle i {
            color: white;
            font-size: 28px;
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            font-weight: 400;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.2s ease;
            background: #ffffff;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            background: #ffffff;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .btn-login {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            border-radius: 8px;
            padding: 13px;
            font-size: 15px;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider span {
            padding: 0 16px;
            color: #94a3b8;
            font-size: 13px;
        }

        .register-text {
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        .register-link {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .register-link:hover {
            color: #4338ca;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            pointer-events: none;
        }

        .form-control.with-icon {
            padding-left: 44px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 40px 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-circle">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h2>Selamat Datang</h2>
                <p class="subtitle">Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" class="form-control with-icon" id="email" name="email" placeholder="nama@email.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" class="form-control with-icon" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-login">Masuk</button>
            </form>

            <div class="divider">
                <span>atau</span>
            </div>

            <p class="register-text">
                Belum punya akun? <a href="{{ route('register') }}" class="register-link">Daftar Sekarang</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>