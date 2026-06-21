<!DOCTYPE html>
<html lang="en">
@vite(['resources/js/app.js'])

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IntellMeet - @yield('title')</title>
    <!-- Alpine.js for dropdown functionality -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .meeting-card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }

        /* Custom scrollbar for notifications */
        .notifications-dropdown::-webkit-scrollbar {
            width: 6px;
        }

        .notifications-dropdown::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .notifications-dropdown::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .notifications-dropdown::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Notification badge pulse animation */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .pulse-animation {
            animation: pulse 1s ease-in-out 3;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left side - Logo and Desktop Navigation -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-video text-indigo-600 text-2xl mr-2"></i>
                        <a href="{{ route('dashboard') }}"
                            class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            IntellMeet
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex md:ml-6 md:space-x-8">
                        <a href="{{ route('dashboard') }}"
                            class="{{ request()->routeIs('dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} 
                        inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>

                        <!-- User Management Button (Admin only) -->
                        @auth
                            @if (auth()->user()->role === 'admin')
                                <a href="{{ route('admin.users.index') }}"
                                    class="{{ request()->routeIs('admin.users.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} 
                                inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    <i class="fas fa-users-cog mr-2"></i>Users
                                </a>
                            @endif
                        @endauth

                        <a href="{{ route('meetings.index') }}"
                            class="{{ request()->routeIs('meetings.*') && !request()->routeIs('meetings.analytics') ? 'border-indigo-500 text-gray-900 border-b-2' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} 
    inline-flex items-center px-1 pt-1 text-sm font-medium">
                            <i class="fas fa-video mr-2"></i>Meetings
                        </a>

                        <a href="{{ route('teams.index') }}"
                            class="{{ request()->routeIs('teams.*') ? 'border-indigo-500 text-gray-900 border-b-2' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} 
    inline-flex items-center px-1 pt-1 text-sm font-medium">
                            <i class="fas fa-users mr-2"></i>Teams
                        </a>

                        <a href="{{ route('tasks.index') }}"
                            class="{{ request()->routeIs('tasks.*') ? 'border-indigo-500 text-gray-900 border-b-2' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} 
    inline-flex items-center px-1 pt-1 text-sm font-medium">
                            <i class="fas fa-tasks mr-2"></i>Tasks
                        </a>

                        <a href="{{ route('ai.insights') }}"
                            class="{{ request()->routeIs('ai.insights') || request()->routeIs('ai.*') ? 'border-indigo-500 text-gray-900 border-b-2' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} 
    inline-flex items-center px-1 pt-1 text-sm font-medium">
                            <i class="fas fa-robot mr-2"></i>AI Insights
                        </a>

                        <a href="{{ route('meetings.analytics') }}"
                            class="{{ request()->routeIs('meetings.analytics') ? 'border-indigo-500 text-gray-900 border-b-2' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} 
    inline-flex items-center px-1 pt-1 text-sm font-medium">
                            <i class="fas fa-chart-line mr-2"></i>Analytics
                        </a>
                    </div>
                </div>

                <!-- Right side - Notifications, User menu, Mobile menu button -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile menu button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Notifications Bell Component -->
                    @include('notification.notification-bell')

                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center space-x-2 focus:outline-none">
                            @php
                                $avatar =
                                    Auth::user()->avatar_url &&
                                    Storage::disk('public')->exists(Auth::user()->avatar_url)
                                        ? asset('storage/' . Auth::user()->avatar_url)
                                        : 'https://ui-avatars.com/api/?name=' .
                                            urlencode(Auth::user()->name ?? 'User') .
                                            '&background=6366f1&color=fff';
                            @endphp

                            <img class="h-8 w-8 rounded-full object-cover" src="{{ $avatar }}" alt="User Avatar">
                            <span
                                class="hidden md:inline-block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'User' }}</span>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>

                        <div x-show="userMenuOpen" @click.away="userMenuOpen = false"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                            <a href="{{ route('profile.show') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="{{ route('settings.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i> Settings
                            </a>
                            <hr class="my-1">
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden bg-white border-t border-gray-200">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} 
                block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>

                <!-- User Management in Mobile Menu (Admin only) -->
                @auth
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.users.index') }}"
                            class="{{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} 
                        block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                            <i class="fas fa-users-cog mr-2"></i> Users
                        </a>
                    @endif
                @endauth

                <a href="{{ route('meetings.index') }}"
                    class="{{ request()->routeIs('meetings.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} 
                block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-video mr-2"></i> Meetings
                </a>

                <a href="{{ route('teams.index') }}"
                    class="{{ request()->routeIs('teams.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} 
                block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-users mr-2"></i> Teams
                </a>

                <a href="{{ route('tasks.index') }}"
                    class="{{ request()->routeIs('tasks.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} 
                block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-tasks mr-2"></i> Tasks
                </a>
            </div>

            <!-- Mobile user info -->
            <div class="pt-4 pb-3 border-t border-gray-200">
                @auth
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full object-cover"
                                src="{{ Auth::user()->avatar_url ? Storage::url(Auth::user()->avatar_url) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=6366f1&color=fff' }}"
                                alt="{{ Auth::user()->name }}">
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Additional Scripts -->
    <script>
        // Show floating notification message
        function showFloatingMessage(message, type = 'success') {
            const div = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            div.className =
                `fixed top-20 left-1/2 transform -translate-x-1/2 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
            div.style.animation = 'bounce 0.5s ease';
            div.innerHTML = `<i class="fas ${icon} mr-2"></i>${message}`;
            document.body.appendChild(div);

            setTimeout(() => {
                div.style.opacity = '0';
                div.style.transition = 'opacity 0.5s ease';
                setTimeout(() => div.remove(), 500);
            }, 3000);
        }

        // Handle logout message from session
        document.addEventListener('DOMContentLoaded', function() {
            // Check for logout parameter in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('logout') === 'success') {
                showFloatingMessage('You have been logged out successfully!');
                // Remove parameter from URL without reload
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Check session storage for logout message
            const logoutMsg = sessionStorage.getItem('logout_message');
            if (logoutMsg) {
                showFloatingMessage(logoutMsg);
                sessionStorage.removeItem('logout_message');
            }

            // Check for success message in session
            @if (session('success'))
                @if (str_contains(session('success'), 'logged out'))
                    sessionStorage.setItem('logout_message', '{{ session('success') }}');
                @endif
            @endif
        });

        // Prevent back button after logout
        if (window.location.pathname === '/login' || window.location.pathname === '/') {
            if (sessionStorage.getItem('just_logged_out') === 'true') {
                // Push a new state to prevent back navigation
                window.history.pushState(null, null, window.location.href);
                window.addEventListener('popstate', function() {
                    window.history.pushState(null, null, window.location.href);
                    showFloatingMessage('You have been logged out successfully!');
                });
                sessionStorage.removeItem('just_logged_out');
            }
        }

        // Session check for authenticated pages
        @auth
        let authCheckInterval = setInterval(function() {
            fetch('/check-auth', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.authenticated) {
                        clearInterval(authCheckInterval);
                        sessionStorage.setItem('just_logged_out', 'true');
                        window.location.href = '/login';
                    }
                })
                .catch(error => {
                    console.error('Auth check failed:', error);
                });
        }, 30000); // Check every 30 seconds
        @endauth

        // Auto-dismiss flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            flashMessages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => message.remove(), 500);
                }, 5000);
            });
        });

        // Real-time notification listener (optional - for WebSocket/Laravel Echo)
        // Uncomment if you have Laravel Echo set up
        /*
        import Echo from 'laravel-echo';
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: process.env.MIX_PUSHER_APP_KEY,
            cluster: process.env.MIX_PUSHER_APP_CLUSTER,
            forceTLS: true
        });
        
        window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
            .notification((notification) => {
                // Play notification sound
                const audio = new Audio('/notification.mp3');
                audio.play();
                
                // Show browser notification if permitted
                if (Notification.permission === 'granted') {
                    new Notification(notification.title, {
                        body: notification.body,
                        icon: '/favicon.ico'
                    });
                }
                
                // Update notification count
                fetch('/notifications/unread-count')
                    .then(response => response.json())
                    .then(data => {
                        const badge = document.querySelector('#notification-badge');
                        if (badge) {
                            if (data.count > 0) {
                                badge.textContent = data.count > 99 ? '99+' : data.count;
                                badge.classList.remove('hidden');
                                badge.classList.add('pulse-animation');
                                setTimeout(() => {
                                    badge.classList.remove('pulse-animation');
                                }, 1000);
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    });
            });
        */

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    </script>

    <style>
        @keyframes bounce {

            0%,
            100% {
                transform: translateX(-50%) translateY(0);
            }

            50% {
                transform: translateX(-50%) translateY(-10px);
            }
        }

        .animate-bounce {
            animation: bounce 0.5s ease;
        }
    </style>

    @stack('scripts')
</body>

</html>
