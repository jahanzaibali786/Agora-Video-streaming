<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - SYncly</title>
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
            align-items: flex-start;
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
            margin-top: 2px;
            flex-shrink: 0;
        }

        .checkbox-group label {
            color: #9ca3af;
            font-size: 14px;
            line-height: 1.5;
        }

        .checkbox-group label a {
            color: #10b981;
            text-decoration: none;
        }

        .checkbox-group label a:hover {
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

        .login-link {
            text-align: center;
            margin-top: 32px;
            color: #9ca3af;
            font-size: 14px;
        }

        .login-link a {
            color: #10b981;
            font-weight: 500;
            text-decoration: none;
        }

        .login-link a:hover {
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
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    
    <div class="container">
        <!-- Logo Section -->
        <div class="logo-section">
            <img src="{{ asset('dark syncly.png') }}" alt="SYncly" class="logo-img">
            <h1>Create Account</h1>
            <p>Join SYncly and start connecting</p>
            <!-- <p class="slogan">sync, speak and share</p> -->
        </div>

        <!-- Form Card -->
        <div class="form-card">
            
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input id="name" 
                           type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           autofocus 
                           autocomplete="name" 
                           class="form-input"
                           placeholder="John Doe">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="email" 
                           class="form-input"
                           placeholder="you@example.com">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="new-password" 
                           class="form-input"
                           placeholder="••••••••">
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input id="password_confirmation" 
                           type="password" 
                           name="password_confirmation" 
                           required 
                           autocomplete="new-password" 
                           class="form-input"
                           placeholder="••••••••">
                    @error('password_confirmation')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Terms -->
                <div class="checkbox-group">
                    <input id="terms" type="checkbox" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    Create Account
                </button>
            </form>

            <!-- Login Link -->
            <div class="login-link">
                Already have an account? 
                <a href="{{ route('login') }}">Sign in</a>
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
