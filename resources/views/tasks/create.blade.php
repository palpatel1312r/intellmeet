<!-- resources/views/tasks/create.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-tasks mr-2 text-indigo-600"></i>
                    Create New Task
                </h1>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data" class="p-6" id="taskForm">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Task Title -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Task Title *</label>
                        <input type="text" name="title" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <!-- Team Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Team *</label>
                        <select name="team_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Team</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}"
                                    {{ $selectedTeam && $selectedTeam->id == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assign To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                        <select name="assigned_to"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select User (Optional)</option>
                            @foreach ($teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                        <select name="priority" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                        <input type="date" name="due_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- File Attachments -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-paperclip mr-1"></i> Attachments
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6" id="dropzone">
                            <div class="text-center">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                <p class="text-gray-600">Drag & drop files here or click to browse</p>
                                <p class="text-xs text-gray-500 mt-1">Supported files: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX,
                                    JPG, PNG, GIF (Max 10MB each)</p>
                                <input type="file" name="attachments[]" multiple
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif" class="hidden"
                                    id="fileInput">
                                <button type="button" onclick="document.getElementById('fileInput').click()"
                                    class="mt-3 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    Browse Files
                                </button>
                            </div>
                        </div>

                        <!-- File List Preview -->
                        <div id="fileList" class="mt-4 space-y-2"></div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                    <a href="{{ route('tasks.index') }}"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // File upload preview
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
        const ALLOWED_TYPES = ['application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg', 'image/png', 'image/gif'
        ];

        let files = [];

        fileInput.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            newFiles.forEach(file => {
                if (validateFile(file)) {
                    files.push(file);
                }
            });
            updateFileList();
        });

        function validateFile(file) {
            if (!ALLOWED_TYPES.includes(file.type)) {
                showFloatingMessage(`File type not allowed: ${file.name}`, 'error');
                return false;
            }
            if (file.size > MAX_FILE_SIZE) {
                showFloatingMessage(`File too large: ${file.name} (Max 10MB)`, 'error');
                return false;
            }
            return true;
        }

        function updateFileList() {
            fileList.innerHTML = '';
            files.forEach((file, index) => {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileIcon = getFileIcon(file.name);

                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                fileItem.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas ${fileIcon} text-2xl"></i>
                <div>
                    <p class="text-sm font-medium text-gray-700">${file.name}</p>
                    <p class="text-xs text-gray-500">${fileSize} MB</p>
                </div>
            </div>
            <button type="button" onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                <i class="fas fa-trash"></i>
            </button>
        `;
                fileList.appendChild(fileItem);
            });

            // Update form data for submission
            updateFormData();
        }

        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                'pdf': 'fa-file-pdf text-red-500',
                'doc': 'fa-file-word text-blue-500',
                'docx': 'fa-file-word text-blue-500',
                'xls': 'fa-file-excel text-green-500',
                'xlsx': 'fa-file-excel text-green-500',
                'ppt': 'fa-file-powerpoint text-orange-500',
                'pptx': 'fa-file-powerpoint text-orange-500',
                'jpg': 'fa-file-image text-purple-500',
                'jpeg': 'fa-file-image text-purple-500',
                'png': 'fa-file-image text-purple-500',
                'gif': 'fa-file-image text-purple-500'
            };
            return icons[ext] || 'fa-file text-gray-500';
        }

        function removeFile(index) {
            files.splice(index, 1);
            updateFileList();
        }

        function updateFormData() {
            const dataTransfer = new DataTransfer();
            files.forEach(file => {
                dataTransfer.items.add(file);
            });
            fileInput.files = dataTransfer.files;
        }

        // Drag and drop functionality
        const dropzone = document.getElementById('dropzone');
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('border-indigo-500', 'bg-indigo-50');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('border-indigo-500', 'bg-indigo-50');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-indigo-500', 'bg-indigo-50');

            const droppedFiles = Array.from(e.dataTransfer.files);
            droppedFiles.forEach(file => {
                if (validateFile(file)) {
                    files.push(file);
                }
            });
            updateFileList();
        });

        function showFloatingMessage(message, type = 'success') {
            // Use your existing showFloatingMessage function
            const div = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            div.className =
                `fixed top-20 left-1/2 transform -translate-x-1/2 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
            div.innerHTML = `<i class="fas ${icon} mr-2"></i>${message}`;
            document.body.appendChild(div);
            setTimeout(() => div.remove(), 3000);
        }
    </script>
@endsection
