<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SYncly') }}</title>
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
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 900px;
            text-align: center;
        }

        .logo-section {
            /* margin-bottom: 32px; */
            animation: fadeInDown 0.8s ease-out;
        }

        .logo-img {
            /* width: 180px; */
            height: 180px;
            object-fit: contain;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .welcome-text {
            animation: fadeIn 1s ease-out 0.3s both;
            margin-bottom: 8px;
        }

        .welcome-text h1 {
            color: #fff;
            font-size: 32px;
            font-weight: 700;
        }

        .slogan-text {
            animation: fadeIn 1s ease-out 0.5s both;
            margin-bottom: 48px;
        }

        .slogan-text p {
            color: #9ca3af;
            font-size: 18px;
            font-style: italic;
        }

        .options-container {
            display: flex;
            justify-content: center;
            gap: 32px;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease-out 0.7s both;
        }

        .option-card {
            width: 260px;
            padding: 32px;
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(145deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
            border: 1px solid rgba(255,255,255,0.1);
            text-decoration: none;
            display: block;
        }

        .option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .option-card.chat:hover {
            border-color: rgba(16, 185, 129, 0.5);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.2);
        }

        .option-card.calling:hover {
            border-color: rgba(245, 158, 11, 0.5);
            box-shadow: 0 20px 40px rgba(245, 158, 11, 0.2);
        }

        .option-card.streaming:hover {
            border-color: rgba(239, 68, 68, 0.5);
            box-shadow: 0 20px 40px rgba(239, 68, 68, 0.2);
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
        }

        .icon-chat {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .icon-calling {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .icon-streaming {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .icon-wrapper svg {
            width: 40px;
            height: 40px;
            color: #fff;
        }

        .option-card h3 {
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .option-card p {
            color: #9ca3af;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .options-container {
                flex-direction: column;
                align-items: center;
            }
            
            .logo-img {
                width: 140px;
                height: 140px;
            }
            
            .welcome-text h1 {
                font-size: 24px;
            }
            
            .slogan-text p {
                font-size: 16px;
            }
        }

        .logout-container {
            margin-top: 40px;
            animation: fadeInUp 0.8s ease-out 0.9s both;
        }

        .logout-btn {
            background: transparent;
            border: 2px solid #ef4444;
            color: #ef4444;
            padding: 12px 32px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .logout-btn:hover {
            background: #ef4444;
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        }

        .logout-btn svg {
            width: 20px;
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    
    <div class="container">
        <!-- Logo Section -->
        <div class="logo-section">
            <img src="{{ asset('dark syncly.png') }}" alt="SYncly Logo" class="logo-img">
        </div>

        <!-- Welcome Message -->
        <div class="welcome-text">
            <h1>Welcome {{ Auth::user()->name }}</h1>
        </div>

        <!-- Slogan -->
        <div class="slogan-text">
            <p>sync, speak and share</p>
        </div>

        <!-- Options Container -->
        <div class="options-container">
            
            <!-- Chat Option -->
            <a href="{{ route('messanger') }}" class="option-card chat">
                <div class="icon-wrapper icon-chat">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h3>Chat</h3>
                <p>Group messaging & conversations</p>
            </a>

            <!-- Calling Option -->
            <a href="{{ route('privatecall') }}" class="option-card calling">
                <div class="icon-wrapper icon-calling">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <h3>Calling</h3>
                <p>Audio & video calls</p>
            </a>

            <!-- Streaming Option -->
            <a href="{{ route('stream.list') }}" class="option-card streaming">
                <div class="icon-wrapper icon-streaming">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3>Streaming</h3>
                <p>Live broadcasts & streams</p>
            </a>

        </div>

        <!-- Logout Button -->
        <div class="logout-container">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</body>
</html>
