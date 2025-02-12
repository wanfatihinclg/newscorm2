<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $sco->title }}</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100vh;
            background: #f5f5f5;
        }
        #scorm-container {
            width: 100%;
            height: calc(100vh - 150px);
            border: none;
        }
        .nav-bar {
            height: 50px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            padding: 0 20px;
            justify-content: space-between;
        }
        .nav-title {
            font-size: 18px;
            font-weight: 500;
        }
        .nav-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .completion-status {
            font-size: 14px;
            color: #666;
        }
        .nav-controls button {
            padding: 8px 15px;
            margin-left: 10px;
            border: none;
            border-radius: 4px;
            background: #007bff;
            color: white;
            cursor: pointer;
        }
        .nav-controls button:hover {
            background: #0056b3;
        }
        .progress-info {
            padding: 20px;
            background: #fff;
            border-bottom: 1px solid #ddd;
        }
        .progress-info .badge {
            font-size: 14px;
        }
        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: #ddd;
        }
        .progress-bar {
            height: 10px;
            border-radius: 5px;
            background-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div class="nav-title">{{ $sco->title }}</div>
        <div class="nav-info">
            <div class="nav-controls">
                <button onclick="window.location.href='{{ route('scorm.show', $scorm->id) }}'">Exit</button>
            </div>
        </div>
    </div>

    <iframe id="scorm-container" src="{{ $launchUrl }}"></iframe>

    <script>
        // Initialize SCORM data
        window.SCORM_DATA = {};

        // Initialize SCORM API
        var API = {
            LMSInitialize: function() {
                console.log('LMSInitialize called');
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '{{ route('scorm.attempt-data', ['scorm' => $scorm->id]) }}?attempt_id={{ $attempt->id }}&sco_id={{ $sco->id }}', false); // false makes the call synchronous
                xhr.send();
                
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        window.SCORM_DATA = response.data;
                        return "true";
                    }
                }
                return "false";
            },

            LMSFinish: function() {
                console.log('LMSFinish called');
                return "true";
            },

            LMSGetValue: function(element) {
                console.log('LMSGetValue called:', element);
                return window.SCORM_DATA[element] || "";
            },

            LMSSetValue: function(element, value) {
                console.log('LMSSetValue called:', element, value);
                window.SCORM_DATA[element] = value;
                
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route('scorm.track', ['scorm' => $scorm->id, 'sco' => $sco->id]) }}', false);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                
                var data = 'attempt_id={{ $attempt->id }}&element=' + encodeURIComponent(element) + '&value=' + encodeURIComponent(value);
                xhr.send(data);
                
                return xhr.status === 200 ? "true" : "false";
            },

            LMSCommit: function() {
                console.log('LMSCommit called');
                return "true";
            },

            LMSGetLastError: function() {
                return "0";
            },

            LMSGetErrorString: function() {
                return "No error";
            },

            LMSGetDiagnostic: function() {
                return "No diagnostic information available.";
            }
        };

        // SCORM 2004 API
        var API_1484_11 = {
            Initialize: function() { 
                console.log('Initialize called (2004)');
                return API.LMSInitialize(); 
            },
            Terminate: function() { 
                console.log('Terminate called (2004)');
                return API.LMSFinish(); 
            },
            GetValue: function(element) { 
                console.log('GetValue called (2004):', element);
                return API.LMSGetValue(element); 
            },
            SetValue: function(element, value) { 
                console.log('SetValue called (2004):', element, value);
                return API.LMSSetValue(element, value); 
            },
            Commit: function() { 
                console.log('Commit called (2004)');
                return API.LMSCommit(); 
            },
            GetLastError: function() { return API.LMSGetLastError(); },
            GetErrorString: function() { return API.LMSGetErrorString(); },
            GetDiagnostic: function() { return API.LMSGetDiagnostic(); }
        };

        // Make APIs available globally
        window.API = API;
        window.API_1484_11 = API_1484_11;
    </script>
</body>
</html>
