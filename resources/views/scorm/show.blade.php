<!DOCTYPE html>
<html>
<head>
    <title>{{ $scorm->name }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Package Details</h3>
                            <a href="{{ route('scorm.index') }}" class="text-blue-600 hover:text-blue-900">Back to List</a>
                        </div>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Version</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $scorm->version }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $scorm->scorm_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Max Grade</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $scorm->max_grade }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Max Attempts</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $scorm->max_attempt }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Content Structure</h3>
                        <div class="space-y-4">
                            @foreach ($scorm->scoes as $sco)
                                <div class="p-4 border rounded-lg hover:bg-gray-50">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="text-md font-medium">{{ $sco->title }}</h4>
                                            <p class="text-sm text-gray-500">Type: {{ $sco->scorm_type }}</p>
                                        </div>
                                        <a href="{{ route('scorm.launch', ['scorm' => $scorm->id, 'sco' => $sco->id]) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            Launch
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
