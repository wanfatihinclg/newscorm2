<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SCORM Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col">
                <h1>SCORM Packages</h1>
            </div>
            <div class="col text-end">
                <a href="{{ route('scorm.create') }}" class="btn btn-primary">Upload New Package</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Version</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scorms as $scorm)
                                <tr>
                                    <td>{{ $scorm->name }}</td>
                                    <td>{{ $scorm->version }}</td>
                                    <td>{{ $scorm->scorm_type }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('scorm.show', $scorm->id) }}" class="btn btn-primary btn-sm">View</a>
                                            <form method="POST" action="{{ route('scorm.destroy', $scorm->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this SCORM package? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
