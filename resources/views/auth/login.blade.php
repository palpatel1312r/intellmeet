<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IntellMeet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-indigo-900 to-purple-900 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full p-4">
                    <i class="fas fa-video text-white text-3xl"></i>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Welcome Back</h2>
            <p class="text-gray-500 mt-2">Sign in to continue to IntellMeet</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-3 text-gray-400"></i>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Enter Your Email">
                </div>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                    <input type="password" name="password" required
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="••••••••">
                </div>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        <!-- Add this inside the login form, after the password field -->
<div class="flex items-center justify-between mb-6">
    <label class="flex items-center">
        <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600">
        <span class="ml-2 text-sm text-gray-600">Remember me</span>
    </label>
    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
        Forgot password?
    </a>
</div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-2 px-4 rounded-lg hover:opacity-90 transition">
                Sign In
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-500 font-semibold">Sign up</a>
        </p>
    </div>
    <script>
        // Clear history when on login page
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Prevent back button after logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                // Page was loaded from cache (back button was used)
                console.log('Page loaded from cache');

                // Check if user is actually logged out
                fetch('/check-auth')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.authenticated) {
                            // Show message that session expired
                            showExpiredSessionMessage();
                        }
                    });
            }
        });

        function showExpiredSessionMessage() {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'fixed top-4 right-4 bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            messageDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>Your session has expired. Please login again.</span>
            </div>
        `;
            document.body.appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.remove();
            }, 4000);
        }
    </script>
</body>

</html>
