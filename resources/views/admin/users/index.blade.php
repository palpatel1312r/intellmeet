@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">User Management</h1>
                <p class="text-gray-500 mt-1">Manage all users in the system</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.users.create') }}"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-200">
                    <i class="fas fa-user-plus mr-2"></i>Add User
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Users</p>
                        <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                    </div>
                    <i class="fas fa-users text-blue-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Active Users</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                    </div>
                    <i class="fas fa-user-check text-green-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Inactive Users</p>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['inactive'] ?? 0 }}</p>
                    </div>
                    <i class="fas fa-user-slash text-red-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Admins</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['admins'] }}</p>
                    </div>
                    <i class="fas fa-user-shield text-purple-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">New This Month</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $stats['new_this_month'] }}</p>
                    </div>
                    <i class="fas fa-calendar-plus text-orange-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex space-x-2">
                <a href="{{ route('admin.users.index', array_merge(request()->all(), ['status' => 'all'])) }}"
                    class="px-4 py-2 rounded-lg transition duration-200 {{ request('status', 'all') == 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    All Users
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->all(), ['status' => 'active'])) }}"
                    class="px-4 py-2 rounded-lg transition duration-200 {{ request('status') == 'active' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    <i class="fas fa-check-circle mr-1"></i> Active
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->all(), ['status' => 'inactive'])) }}"
                    class="px-4 py-2 rounded-lg transition duration-200 {{ request('status') == 'inactive' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    <i class="fas fa-ban mr-1"></i> Inactive
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" placeholder="Search by name or email..."
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <select name="role"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admins</option>
                    <option value="member" {{ request('role') == 'member' ? 'selected' : '' }}>Members</option>
                </select>
                <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition duration-200">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition duration-200">
                    <i class="fas fa-undo mr-2"></i>Reset
                </a>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover"
                                            src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff' }}"
                                            alt="">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.changeRole', $user) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <select name="role" onchange="this.form.submit()"
                                                class="text-sm border rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <option value="member" {{ $user->role == 'member' ? 'selected' : '' }}>
                                                    Member</option>
                                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin
                                                </option>
                                            </select>
                                        </form>
                                    @else
                                        <span
                                            class="text-sm font-semibold text-purple-600">{{ ucfirst($user->role) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user->trashed())
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-ban mr-1"></i>Inactive
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Active
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-3">

                                        <a href="{{ route('admin.users.show', $user) }}"
                                            class="text-blue-600 hover:text-blue-800 transition duration-200 text-xl p-1"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- <a href="{{ route('admin.users.edit', $user) }}"
                                            class="text-green-600 hover:text-green-800 transition duration-200 text-xl p-2"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a> --}}

                                        @if ($user->trashed())
                                            <form action="{{ route('admin.users.restore', $user->id) }}" method="POST"
                                                class="inline restore-form" data-user-id="{{ $user->id }}">
                                                @csrf
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-800 transition duration-200 text-xl p-1"
                                                    title="Activate User">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.users.forceDelete', $user->id) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirmPermanentDelete('{{ addslashes($user->name) }}')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-800 transition duration-200 text-xl p-2"
                                                    title="Permanently Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @else
                                            @if ($user->id !== auth()->id())
                                                <button type="button"
                                                    onclick="showDeactivationModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ route('admin.users.destroy', $user) }}')"
                                                    class="text-yellow-600 hover:text-yellow-800 transition duration-200 text-xl p-2"
                                                    title="Deactivate User">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-users-slash text-4xl mb-2"></i>
                                    <p>No users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- Deactivation Modal (Single User) -->
    <div id="deactivationModal" class="fixed inset-0 z-50 hidden overflow-y-auto" style="font-family: system-ui;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal()"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto transform transition-all">
                <div class="p-6">
                    <div class="flex items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-4 mt-2 text-left">
                            <h3 class="text-lg font-medium text-gray-900">Deactivate User?</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Deactivate <strong class="text-yellow-700" id="userNameDisplay"></strong>? They can be
                                    restored later.
                                </p>
                                <div class="mt-3 p-3 bg-yellow-50 rounded-md border border-yellow-100">
                                    <p class="text-xs text-yellow-800 font-semibold">What happens:</p>
                                    <ul class="mt-1 text-xs text-yellow-700 list-disc list-inside">
                                        <li>They will lose access to their account</li>
                                        <li>Their profile will be hidden</li>
                                        <li>They won't receive notifications</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-3">
                    <button type="button" onclick="confirmDeactivation()"
                        class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition duration-200">
                        Deactivate
                    </button>
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 shadow-xl text-center">
                <svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p id="loadingMessage" class="mt-4 text-gray-600">Processing...</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <script>
        let currentUserId = null;
        let currentDeactivateUrl = null;

        // ==================== SINGLE USER DEACTIVATION ====================
        function showDeactivationModal(userId, userName, deactivateUrl) {
            currentUserId = userId;
            currentDeactivateUrl = deactivateUrl;
            document.getElementById('userNameDisplay').textContent = userName;
            document.getElementById('deactivationModal').classList.remove('hidden');
            document.getElementById('deactivationModal').style.animation = 'modalSlideIn 0.3s ease-out';
        }

        function closeModal() {
            document.getElementById('deactivationModal').classList.add('hidden');
            currentUserId = null;
            currentDeactivateUrl = null;
        }

        function confirmDeactivation() {
            if (currentUserId && currentDeactivateUrl) {
                showLoading('Deactivating user...');
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = currentDeactivateUrl;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // ==================== PERMANENT DELETE CONFIRMATION ====================
        function confirmPermanentDelete(userName) {
            return confirm(
                `⚠️ WARNING: Permanently delete ${userName}?\n\nThis action CANNOT be undone! All user data will be permanently removed from the system.`
            );
        }

        // ==================== LOADING OVERLAY ====================
        function showLoading(message = 'Processing...') {
            const overlay = document.getElementById('loadingOverlay');
            const messageElement = document.getElementById('loadingMessage');
            if (messageElement) messageElement.textContent = message;
            if (overlay) overlay.classList.remove('hidden');
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.classList.add('hidden');
        }

        // ==================== TOAST NOTIFICATIONS ====================
        function showToast(type, message) {
            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.className = 'fixed bottom-4 right-4 z-50 space-y-2';
                document.body.appendChild(toastContainer);
            }

            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-yellow-500');
            const icon = type === 'success' ? '✓' : (type === 'error' ? '✗' : '⚠');

            toast.className =
                `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0 opacity-100 flex items-center space-x-2 min-w-[250px]`;
            toast.style.animation = 'modalSlideIn 0.3s ease-out';
            toast.innerHTML = `
                <span class="font-bold text-lg">${icon}</span>
                <span class="text-sm">${message}</span>
            `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ==================== KEYBOARD SHORTCUTS ====================
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Hide loading overlay when page finishes loading
        window.addEventListener('load', function() {
            hideLoading();
        });

        // Add loading to restore forms
        document.querySelectorAll('.restore-form').forEach(form => {
            form.addEventListener('submit', function() {
                showLoading('Activating user...');
            });
        });
    </script>
@endsection
