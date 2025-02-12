<!DOCTYPE html>
<html>
<head>
    <title>Upload SCORM Package</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between mb-6">
                        <h3 class="text-lg font-semibold">Upload SCORM Package</h3>
                        <a href="{{ route('scorm.index') }}" class="text-blue-600 hover:text-blue-900">Back to List</a>
                    </div>

                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <div class="font-medium">Please fix the following errors:</div>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('scorm.upload') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label for="course_id" class="block text-sm font-medium text-gray-700">Course</label>
                            <select id="course_id" name="course_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700">SCORM Package (ZIP)</label>
                            <input type="file" id="file" name="file" accept=".zip" class="mt-1 block w-full" required>
                            <p class="mt-1 text-sm text-gray-500">Please upload a valid SCORM package in ZIP format.</p>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Upload Package
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            const fileInput = document.querySelector('#file');
            const submitBtn = document.querySelector('#submitBtn');
            
            if (!fileInput.files.length) {
                alert('Please select a file to upload');
                return;
            }
            
            // Check file size (max 100MB)
            const maxSize = 100 * 1024 * 1024; // 100MB in bytes
            if (fileInput.files[0].size > maxSize) {
                alert('File is too large. Maximum size is 100MB.');
                return;
            }
            
            // Check file type
            if (!fileInput.files[0].name.toLowerCase().endsWith('.zip')) {
                alert('Please select a ZIP file.');
                return;
            }
            
            // Disable submit button and show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Submit the form
            this.submit();
        });
    </script>
</body>
</html>
