<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SYncly</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #000;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-img {
            /* width: 112px;
            height: 112px; */
            object-fit: contain;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        .logo-section h1 {
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            /* margin-top: 32px; */
            letter-spacing: -0.5px;
        }

        .logo-section p {
            color: #9ca3af;
            font-size: 14px;
            margin-top: 8px;
        }

        .logo-section .slogan {
            color: #10b981;
            font-size: 14px;
            font-weight: 500;
            margin-top: 8px;
        }

        .form-card {
            background: rgba(20, 20, 20, 0.85);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 20px;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .form-input {
            width: 100%;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: #10b981;
            box-shadow: 
                0 0 0 3px rgba(16, 185, 129, 0.15),
                0 0 25px rgba(16, 185, 129, 0.1);
            outline: none;
        }

        .form-error {
            color: #f87171;
            font-size: 14px;
            margin-top: 8px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 32px;
        }

        .checkbox-group input {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid #4b5563;
            background: rgba(255, 255, 255, 0.05);
            accent-color: #10b981;
            margin-right: 10px;
        }

        .checkbox-group label {
            color: #9ca3af;
            font-size: 14px;
        }

        .forgot-link {
            color: #10b981;
            font-size: 12px;
            text-decoration: none;
        }

        .forgot-link:hover {
            color: #34d399;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 20px 0;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.3), transparent);
        }

        .divider-text {
            color: #6b7280;
            font-size: 14px;
        }

        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .social-btn {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(16, 185, 129, 0.3);
            transform: translateY(-2px);
        }

        .social-btn svg {
            width: 24px;
            height: 24px;
        }

        .register-link {
            text-align: center;
            margin-top: 32px;
            color: #9ca3af;
            font-size: 14px;
        }

        .register-link a {
            color: #10b981;
            font-weight: 500;
            text-decoration: none;
        }

        .register-link a:hover {
            color: #34d399;
        }

        .back-link {
            text-align: center;
            margin-top: 32px;
        }

        .back-link a {
            color: #6b7280;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-link a:hover {
            color: #10b981;
        }

        .back-link svg {
            width: 16px;
            height: 16px;
        }

        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    
    <div class="container">
        <!-- Logo Section -->
        <div class="logo-section">
            <img src="{{ asset('dark syncly.png') }}" alt="SYncly" class="logo-img">
            <h1>Welcome Back</h1>
            <p>Sign in to continue to SYncly</p>
            <!-- <p class="slogan">sync, speak and share</p> -->
        </div>

        <!-- Form Card -->
        <div class="form-card">
            
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username" 
                           class="form-input"
                           placeholder="you@example.com">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <label for="password" class="form-label" style="margin-bottom: 0;">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <input id="password" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="current-password" 
                           class="form-input"
                           placeholder="••••••••">
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="checkbox-group">
                    <input id="remember_me" type="checkbox" name="remember">
                    <label for="remember_me">Remember me</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    Sign In
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <div class="divider-line"></div>
                <span class="divider-text">or continue with</span>
                <div class="divider-line"></div>
            </div>

            <!-- Social Buttons -->
            <div class="social-buttons">
                <button type="button" class="social-btn">
                    <svg viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                </button>
                <button type="button" class="social-btn">
                    <svg fill="#fff" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </button>
            </div>

            <!-- Register Link -->
            <div class="register-link">
                Don't have an account? 
                <a href="{{ route('register') }}">Create one</a>
            </div>
        </div>

        <!-- Back Link -->
        <div class="back-link">
            <a href="/">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to home
            </a>
        </div>
    </div>
</body>
</html>
