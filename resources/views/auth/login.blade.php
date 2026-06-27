<?php $page = 'signin'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - POS KasirPro</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('/build/img/favicon.png') }}">
    <link rel="stylesheet" href="{{ URL::asset('/build/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('/build/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('/build/plugins/fontawesome/css/all.min.css') }}">
    <style>
        :root { --primary: #FF9F43; --secondary: #092C4C; }
        body { font-family: 'Nunito', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(135deg, #092C4C 0%, #1a3a5c 100%); margin: 0; }
        .login-card { background: #fff; border-radius: 12px; padding: 40px; width: 400px; max-width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .login-card h3 { font-weight: 700; color: #333; margin-bottom: 5px; }
        .login-card p { color: #888; margin-bottom: 25px; font-size: 14px; }
        .login-card .form-control { border-radius: 8px; padding: 12px 16px; border: 1.5px solid #e0e0e0; font-size: 14px; }
        .login-card .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255,159,67,0.15); }
        .login-card .btn-login { background: var(--primary); border: none; padding: 12px; border-radius: 8px; font-size: 16px; font-weight: 600; width: 100%; color: #fff; }
        .login-card .btn-login:hover { background: #e68a2e; }
        .login-card .form-label { font-weight: 600; color: #555; font-size: 13px; margin-bottom: 6px; }
        .login-card .input-group-text { background: #f8f9fa; border: 1.5px solid #e0e0e0; border-right: none; border-radius: 8px 0 0 8px; }
        .login-card .input-group .form-control { border-left: none; border-radius: 0 8px 8px 0; }
        .login-logo { text-align: center; margin-bottom: 30px; }
        .login-logo img { height: 50px; }
        .alert { border-radius: 8px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <img src="{{ URL::asset('/build/img/logo.png') }}" alt="POS KasirPro">
        </div>
        <h3>Welcome Back!</h3>
        <p>Sign in to your account to continue</p>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" class="form-control" name="email" value="{{ old('email', 'admin@kasirpro.com') }}" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control" name="password" value="12345678" required>
                </div>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">Demo: admin@kasirpro.com / 12345678</small>
        </div>
    </div>
</body>
</html>
