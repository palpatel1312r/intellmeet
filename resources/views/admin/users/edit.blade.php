@extends('layouts.app')

@section('title', 'Edit ' . $user->name)

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex items-center">
                    <a href="{{ route('admin.users.index') }}" class="text-white hover:text-indigo-200 mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold">Edit User</h1>
                        <p class="text-indigo-100">Update user information</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Full Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Email Address *</label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">New Password (optional)</label>
                        <input type="password" name="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Leave blank to keep current">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Confirm new password">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Role</label>
                    <select name="role"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="member" {{ old('role', $user->role) == 'member' ? 'selected' : '' }}>Member</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('admin.users.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>Update User
                    </button>
                </div>
            </form>

            <!-- Danger Zone -->
            @if ($user->id !== auth()->id())
                <div class="p-6 border-t border-red-200 bg-red-50">
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Danger Zone</h3>
                    <p class="text-sm text-red-600 mb-4">Once deleted, user data cannot be recovered.</p>
                    <!-- Delete User Button -->
                    <button type="button"
                        onclick="showDeleteModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ route('admin.users.destroy', $user) }}')"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg transform hover:scale-105 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Delete User</span>
                    </button>

                    <!-- Delete User Modal -->
                    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" style="font-family: system-ui;">
                        <div
                            class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                            <!-- Background overlay -->
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                onclick="closeDeleteModal()"></div>

                            <!-- Modal panel -->
                            <div
                                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <!-- Danger Icon -->
                                        <div
                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>

                                        <!-- Modal Content -->
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                                Delete User Permanently?
                                            </h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">
                                                    Are you sure you want to permanently delete <strong class="text-red-600"
                                                        id="deleteUserName"></strong>?
                                                </p>

                                                <!-- Warning Box -->
                                                <div class="mt-4 p-3 bg-red-50 rounded-md border border-red-100">
                                                    <div class="flex items-start">
                                                        <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <div class="ml-2">
                                                            <p class="text-xs text-red-800 font-semibold">⚠️ This action
                                                                CANNOT be undone!</p>
                                                            <ul
                                                                class="mt-2 text-xs text-red-700 list-disc list-inside space-y-1">
                                                                <li>All user data will be permanently removed</li>
                                                                <li>User will lose access forever</li>
                                                                <li>This action is irreversible</li>
                                                                <li>All associated records will be deleted</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Additional Warning for Admin Users -->
                                                <div id="adminWarning"
                                                    class="mt-3 p-3 bg-orange-50 rounded-md border border-orange-100 hidden">
                                                    <div class="flex items-start">
                                                        <svg class="h-5 w-5 text-orange-400 mt-0.5" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <div class="ml-2">
                                                            <p class="text-xs text-orange-800">
                                                                <span class="font-semibold">Note:</span> This user has
                                                                admin privileges. Deleting them may affect system
                                                                administration.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Buttons -->
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <form id="deleteUserForm" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" id="confirmDeleteBtn"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition duration-200">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Yes, Delete Permanently
                                        </button>
                                    </form>
                                    <button type="button" onclick="closeDeleteModal()"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition duration-200">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        let currentDeleteUserId = null;
                        let currentDeleteUrl = null;
                        let currentDeleteUserName = null;

                        function showDeleteModal(userId, userName, deleteUrl) {
                            currentDeleteUserId = userId;
                            currentDeleteUrl = deleteUrl;
                            currentDeleteUserName = userName;

                            // Set user name in modal
                            document.getElementById('deleteUserName').textContent = userName;

                            // Set form action
                            const form = document.getElementById('deleteUserForm');
                            form.action = deleteUrl;

                            // Optional: Check if user is admin and show warning
                            checkIfUserIsAdmin(userId);

                            // Show modal with animation
                            const modal = document.getElementById('deleteModal');
                            modal.classList.remove('hidden');
                            modal.style.animation = 'modalSlideIn 0.3s ease-out';

                            // Prevent body scrolling
                            document.body.style.overflow = 'hidden';
                        }

                        function closeDeleteModal() {
                            const modal = document.getElementById('deleteModal');
                            modal.classList.add('hidden');
                            document.body.style.overflow = '';
                            currentDeleteUserId = null;
                            currentDeleteUrl = null;
                            currentDeleteUserName = null;
                        }

                        // Optional: Check if user is admin
                        function checkIfUserIsAdmin(userId) {
                            // You can implement an AJAX call to check if user has admin role
                            // For now, this is optional
                            /*
                            fetch(`/admin/users/${userId}/check-role`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.is_admin) {
                                        document.getElementById('adminWarning').classList.remove('hidden');
                                    } else {
                                        document.getElementById('adminWarning').classList.add('hidden');
                                    }
                                });
                            */
                        }

                        // Add loading state when form is submitted
                        document.getElementById('deleteUserForm').addEventListener('submit', function(e) {
                            const confirmBtn = document.getElementById('confirmDeleteBtn');

                            // Show loading state
                            showLoading('Deleting user permanently...');

                            // Disable button and change text
                            confirmBtn.disabled = true;
                            confirmBtn.innerHTML = `
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Deleting...
        `;

                            // Close modal
                            closeDeleteModal();
                        });

                        // Close modal on escape key
                        document.addEventListener('keydown', function(event) {
                            if (event.key === 'Escape') {
                                closeDeleteModal();
                            }
                        });
                    </script>
                </div>
            @endif
        </div>
    </div>
@endsection
