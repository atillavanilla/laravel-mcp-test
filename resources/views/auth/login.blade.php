<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
        }

        .login-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .login-header {
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        }

        .app-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
        }

        .app-icon svg { width: 28px; height: 28px; color: #fff; }

        .login-header h1 { font-size: 1.25rem; font-weight: 700; color: #f1f5f9; margin-bottom: 0.25rem; }
        .login-header p { font-size: 0.875rem; color: #94a3b8; }

        .login-form { padding: 1.5rem 2rem 2rem; }

        .form-group { margin-bottom: 1.25rem; }

        .form-group label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 0.5rem;
            color: #f1f5f9;
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .form-group input::placeholder { color: #475569; }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .remember-row input[type="checkbox"] {
            accent-color: #6366f1;
            width: 16px;
            height: 16px;
        }

        .remember-row label {
            font-size: 0.8125rem;
            color: #94a3b8;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 0.6875rem;
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            color: #fff;
            border: none;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #818cf8, #8b5cf6);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            transform: translateY(-1px);
        }

        .btn-login:active { transform: translateY(0); }

        .error-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
        }

        .error-box p {
            font-size: 0.8125rem;
            color: #fca5a5;
        }

        .powered-by {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.6875rem;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="app-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                </div>
                <h1>Sign In</h1>
                <p>Log in to authorize the application</p>
            </div>

            <form method="POST" action="/login" class="login-form">
                @csrf

                @if($errors->any())
                    <div class="error-box">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Your password" required>
                </div>

                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>
        </div>

        <p class="powered-by">Secured by {{ config('app.name') }}</p>
    </div>
</body>
</html>
