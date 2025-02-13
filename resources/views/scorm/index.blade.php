<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SCORM Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .pagination {
            margin-bottom: 0;
        }
        .pagination .page-link {
            padding: 0.375rem 0.75rem;
            position: relative;
            display: block;
            color: #0d6efd;
            text-decoration: none;
            background-color: #fff;
            border: 1px solid #dee2e6;
            transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
        .pagination .page-item.active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }
        .pagination .page-link:hover {
            z-index: 2;
            color: #0a58ca;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        .pagination-info {
            color: #6c757d;
            font-size: 0.875rem;
        }
        .table > :not(caption) > * > * {
            padding: 0.75rem;
            vertical-align: middle;
        }
    </style>
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
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Version</th>
                                <th>Type</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($scorms as $scorm)
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
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No SCORM packages found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($scorms->hasPages())
                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                        <div class="pagination-info">
                            Showing {{ $scorms->firstItem() }} to {{ $scorms->lastItem() }} of {{ $scorms->total() }} entries
                        </div>
                        {{ $scorms->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
