@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-gray-200">
                <div class="flex overflow-x-auto">
                    <button onclick="showTab('profile')" id="tab-profile"
                        class="px-6 py-3 text-sm font-medium border-b-2 border-indigo-500 text-indigo-600">
                        <i class="fas fa-user mr-2"></i>Profile
                    </button>
                    <button onclick="showTab('password')" id="tab-password"
                        class="px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        <i class="fas fa-lock mr-2"></i>Change Password
                    </button>
                </div>
            </div>

            <div class="p-6">
                <!-- Profile Settings Tab -->
                <div id="profile-tab" class="tab-content">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Profile Settings</h2>

                    <!-- Display Session Messages -->
                    {{-- @if (session('success'))
                        <div id="profile-success-message"
                            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div id="profile-error-message"
                            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        </div>
                    @endif --}}

                    <form id="profile-form" action="{{ route('settings.update-profile') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Full Name</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                                    @error('email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Bio</label>
                                <textarea name="bio" rows="3"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('bio', $user->bio) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Company</label>
                                    <input type="text" name="company" value="{{ old('company', $user->company) }}"
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Position</label>
                                    <input type="text" name="position" value="{{ old('position', $user->position) }}"
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Phone</label>
                                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                                    Save Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Password Settings Tab -->
                <div id="password-tab" class="tab-content hidden">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Change Password</h2>

                    <!-- In-page Message Container -->
                    <div id="password-message-container"></div>

                    <form id="password-form" method="POST" action="{{ route('settings.update-password') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <!-- Current Password -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Current Password</label>
                                <div class="relative">
                                    <input type="password" name="current_password" id="current_password" required
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-10">
                                    <button type="button" onclick="togglePassword('current_password')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="current_password_error" class="text-red-500 text-sm mt-1 hidden"></div>
                            </div>

                            <!-- New Password -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">New Password</label>
                                <div class="relative">
                                    <input type="password" name="password" id="password" required
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-10">
                                    <button type="button" onclick="togglePassword('password')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="password_error" class="text-red-500 text-sm mt-1 hidden"></div>
                            </div>

                            <!-- Confirm New Password -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="password_confirmation" required
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-10">
                                    <button type="button" onclick="togglePassword('password_confirmation')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="password_confirmation_error" class="text-red-500 text-sm mt-1 hidden"></div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="resetPasswordForm()"
                                    class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                                    Reset
                                </button>
                                <button type="submit"
                                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                                    Update Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- <!-- Notifications Tab -->
                <div id="notifications-tab" class="tab-content hidden">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Notification Preferences</h2>

                    <!-- Display Session Messages -->
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('settings.update-notifications') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b">
                                <div>
                                    <p class="font-medium">Email Meeting Reminders</p>
                                    <p class="text-sm text-gray-500">Get email reminders before meetings</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_meeting_reminders" value="1"
                                        class="sr-only peer"
                                        {{ $preferences['notifications']['email_meeting_reminders'] ?? true ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b">
                                <div>
                                    <p class="font-medium">Task Assignment Emails</p>
                                    <p class="text-sm text-gray-500">Get notified when tasks are assigned to you</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_task_assigned" value="1"
                                        class="sr-only peer"
                                        {{ $preferences['notifications']['email_task_assigned'] ?? true ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b">
                                <div>
                                    <p class="font-medium">Team Invitations</p>
                                    <p class="text-sm text-gray-500">Get notified when invited to a team</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_team_invites" value="1" class="sr-only peer"
                                        {{ $preferences['notifications']['email_team_invites'] ?? true ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                                    Save Preferences
                                </button>
                            </div>
                        </div>
                    </form>
                </div> --}}
            </div>
        </div>
    </div>

    <script>
        // Tab switching function
        function showTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

            // Remove active class from all tab buttons
            document.querySelectorAll('[id^="tab-"]').forEach(btn => {
                btn.classList.remove('border-indigo-500', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected tab
            const selectedTab = document.getElementById(`${tab}-tab`);
            if (selectedTab) {
                selectedTab.classList.remove('hidden');
            }

            // Activate selected button
            const activeBtn = document.getElementById(`tab-${tab}`);
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-gray-500');
                activeBtn.classList.add('border-indigo-500', 'text-indigo-600');
            }

            // Update URL hash without scrolling
            window.location.hash = tab;
        }

        // Check for hash in URL on page load
        function checkHash() {
            const hash = window.location.hash.substring(1);
            if (hash && ['profile', 'password', 'notifications'].includes(hash)) {
                showTab(hash);
            }
        }

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);

            // Toggle eye icon
            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        }

        // Show in-page message
        function showMessage(message, type = 'error') {
            const container = document.getElementById('password-message-container');
            const messageDiv = document.createElement('div');
            messageDiv.className =
                `mb-4 p-3 rounded ${type === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'}`;
            messageDiv.innerHTML =
                `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}`;

            // Clear previous messages
            container.innerHTML = '';
            container.appendChild(messageDiv);

            // Auto hide after 5 seconds
            setTimeout(() => {
                messageDiv.style.opacity = '0';
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 300);
            }, 5000);
        }

        // Clear all error messages
        function clearErrors() {
            document.getElementById('current_password_error').classList.add('hidden');
            document.getElementById('password_error').classList.add('hidden');
            document.getElementById('password_confirmation_error').classList.add('hidden');
            document.getElementById('current_password_error').innerHTML = '';
            document.getElementById('password_error').innerHTML = '';
            document.getElementById('password_confirmation_error').innerHTML = '';
        }

        // Reset password form
        function resetPasswordForm() {
            const form = document.getElementById('password-form');
            if (form) {
                form.reset();
            }
            clearErrors();
            document.getElementById('password-message-container').innerHTML = '';
        }

        // Client-side validation
        document.getElementById('password-form')?.addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            let hasError = false;

            clearErrors();

            // Check current password
            if (!currentPassword) {
                document.getElementById('current_password_error').innerHTML = 'Current password is required';
                document.getElementById('current_password_error').classList.remove('hidden');
                hasError = true;
            }

            // Check new password
            if (!password) {
                document.getElementById('password_error').innerHTML = 'New password is required';
                document.getElementById('password_error').classList.remove('hidden');
                hasError = true;
            } else if (password.length < 4) {
                document.getElementById('password_error').innerHTML = 'Password must be at least 4 characters long';
                document.getElementById('password_error').classList.remove('hidden');
                hasError = true;
            }

            // Check password confirmation
            if (password !== confirmPassword) {
                document.getElementById('password_confirmation_error').innerHTML = 'New passwords do not match';
                document.getElementById('password_confirmation_error').classList.remove('hidden');
                hasError = true;
            }

            // if (hasError) {
            //     e.preventDefault();
            //     showMessage('Please fix the errors above before submitting.', 'error');
            //     return false;
            // }

            return true;
        });

        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('#profile-success-message, #profile-error-message').forEach(msg => {
                if (msg) {
                    setTimeout(() => {
                        msg.style.opacity = '0';
                        setTimeout(() => {
                            if (msg.parentNode) {
                                msg.remove();
                            }
                        }, 300);
                    }, 3000);
                }
            });
        }, 1000);

        // Display any PHP session errors on password tab
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                showTab('password');
                @foreach ($errors->all() as $error)
                    showMessage('{{ $error }}', 'error');
                @endforeach
            });
        @endif

        @if (session('success') && request()->routeIs('settings.update-password'))
            document.addEventListener('DOMContentLoaded', function() {
                showTab('password');
                showMessage('{{ session('success') }}', 'success');
            });
        @endif

        // Run on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkHash();
        });
    </script>
@endsection
