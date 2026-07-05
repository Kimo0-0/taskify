<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Taskify Shared</title>
    <link rel="stylesheet" href="/css/MCT.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/all.min.css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <style>
        .public-page {
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: 100vh;
            background-color: var(--bg-color);
        }
        .public-nav {
            background-color: var(--nav-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .public-logo {
            font-family: var(--font-accent);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .public-container {
            flex-grow: 1;
            padding: 40px 24px;
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }
        .theme-toggle-btn {
            background: var(--close-btn-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            width: 40px;
            height: 40px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
        }
        .theme-toggle-btn:hover {
            background: var(--close-btn-hover);
            transform: scale(1.05);
        }
    </style>
</head>
<body class="@yield('body-class')">
    <div class="public-page">
        <nav class="public-nav">
            <a href="#" class="public-logo">
                <i class="fa-solid fa-square-check"></i> Taskify
            </a>
            <div style="display: flex; align-items: center; gap: 16px;">
                <button class="theme-toggle-btn" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="fa-solid fa-moon dark-only"></i>
                    <i class="fa-solid fa-sun light-only"></i>
                </button>
            </div>
        </nav>
        <div class="public-container">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios@1.16.0/dist/axios.min.js"></script>
    <script>
        // Configure Axios to send CSRF token with every request
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function toggleTheme() {
            const html = document.documentElement;
            if (html.classList.contains('dark-mode')) {
                html.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark-mode');
            const sun = document.querySelector('.theme-toggle-btn .fa-sun');
            const moon = document.querySelector('.theme-toggle-btn .fa-moon');
            if (isDark) {
                if (sun) sun.style.display = 'block';
                if (moon) moon.style.display = 'none';
            } else {
                if (sun) sun.style.display = 'none';
                if (moon) moon.style.display = 'block';
            }
        }
        // Initialize icons
        document.addEventListener('DOMContentLoaded', updateThemeIcon);
    </script>

    {{-- Real-Time WebSocket Client (for shared pages) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY', 'task-manager-key-secret') }}',
            wsHost: '{{ env('REVERB_HOST', 'localhost') }}',
            wsPort: {{ env('REVERB_PORT', 8080) }},
            wssPort: {{ env('REVERB_PORT', 8080) }},
            forceTLS: {{ env('REVERB_SCHEME', 'http') === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
        });
    </script>

    {{-- Page-specific real-time channel subscriptions --}}
    @yield('realtime-script')

</body>
</html>
