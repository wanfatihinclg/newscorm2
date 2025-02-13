<?php

namespace App\Http\Controllers;

use App\Models\Scorm;
use App\Models\ScormSco;
use App\Models\Course; 
use App\Models\ScormAttempt; // Added this line
use App\Models\ScormElement;
use App\Models\ScormScoValue;
use App\Services\ScormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ScormController extends Controller
{
    protected $scormService;

    public function __construct(ScormService $scormService)
    {
        $this->scormService = $scormService;
    }

    public function index()
    {
        $scorms = Scorm::with('course')->paginate(70);
        return view('scorm.index', compact('scorms'));
    }

    public function create()
    {
        $courses = Course::all();
        return view('scorm.create', compact('courses'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:zip',
                'course_id' => 'required|exists:courses,id'
            ]);

            Log::info('Starting SCORM upload', [
                'course_id' => $request->course_id,
                'file' => $request->file('file')->getClientOriginalName()
            ]);

            $scorm = $this->scormService->uploadPackage($request->file('file'), $request->course_id);
            
            Log::info('SCORM upload successful', ['scorm_id' => $scorm->id]);
            
            return redirect()->route('scorm.show', $scorm->id)
                ->with('success', 'SCORM package uploaded successfully.');
                
        } catch (\Exception $e) {
            Log::error('SCORM upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to upload SCORM package: ' . $e->getMessage()]);
        }
    }

    public function show(Scorm $scorm)
    {
        $scorm->load('scoes');
        return view('scorm.show', compact('scorm'));
    }

    public function launch(Scorm $scorm, ScormSco $sco)
    {
        if (!$sco->launch) {
            abort(404);
        }

        $basePath = public_path('scorm_files/' . $scorm->reference);
        $launchFile = $sco->launch;
        
        // Get existing attempt or create new one
        $attempt = ScormAttempt::where('scorm_id', $scorm->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$attempt) {
            $attempt = ScormAttempt::create([
                'scorm_id' => $scorm->id,
                'attempt' => 1
            ]);
        }

        $launchPath = $basePath . '/' . $launchFile;
        if (!file_exists($launchPath)) {
            abort(404, 'SCORM content file not found');
        }
        
        // Convert to URL and add parameters if they exist
        $launchUrl = asset('scorm_files/' . $scorm->reference . '/' . $launchFile);
        if (!empty($sco->parameters)) {
            $connector = (strpos($launchUrl, '?') !== false) ? '&' : '?';
            $launchUrl .= $connector . ltrim($sco->parameters, '?&');
        }

        return view('scorm.launch', compact(
            'scorm',
            'sco',
            'attempt',
            'launchUrl'
        ));
    }

    public function track(Request $request, Scorm $scorm, ScormSco $sco)
    {
        try {
            $validated = $request->validate([
                'attempt_id' => 'required|exists:scorm_attempts,id',
                'element' => 'required|string',
                'value' => 'required|string'
            ]);

            $attempt = ScormAttempt::findOrFail($validated['attempt_id']);
            $element = $validated['element'];
            $value = $validated['value'];

            // Create or update the tracking value
            $attempt->values()->updateOrCreate(
                [
                    'scorm_sco_id' => $sco->id,
                    'element' => $element
                ],
                [
                    'value' => $value
                ]
            );

            // Handle completion status for both SCORM 1.2 and 2004
            if ($element === 'cmi.core.lesson_status' || $element === 'cmi.completion_status') {
                $completedStates = ['completed', 'passed', 'complete'];
                if (in_array(strtolower($value), $completedStates)) {
                    $attempt->completed_at = now();
                }
                $attempt->status = strtolower($value);
                $attempt->save();
            }

            // Handle lesson location/bookmark for both versions
            if ($element === 'cmi.core.lesson_location' || $element === 'cmi.location') {
                $attempt->last_location = $value;
                $attempt->save();
            }

            // Handle suspend data for both versions
            if ($element === 'cmi.suspend_data') {
                $attempt->suspend_data = $value;
                $attempt->save();
            }

            // Handle total time for both versions
            if ($element === 'cmi.core.total_time' || $element === 'cmi.total_time') {
                $attempt->total_time = $value;
                $attempt->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tracking data saved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving SCORM tracking data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save tracking data'
            ], 500);
        }
    }

    public function getAttemptData(Request $request, Scorm $scorm)
    {
        try {
            $data = $this->scormService->getAttemptData(
                $request->attempt_id,
                $request->sco_id
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting SCORM attempt data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get attempt data'
            ], 500);
        }
    }

    public function progress(ScormAttempt $attempt)
    {
        return response()->json([
            'status' => $attempt->getCompletionStatus(),
            'percentage' => $attempt->getCompletionPercentage(),
            'totalTime' => $attempt->getTotalTime()
        ]);
    }

    /**
     * Delete the SCORM package and its associated data.
     */
    public function destroy(Scorm $scorm)
    {
        try {
            // Get directory path before deleting the record
            $directory = public_path('scorm_files/' . $scorm->reference);
            
            // Delete from database first
            $scorm->delete();

            // Then delete the directory if it exists
            if (File::exists($directory)) {
                File::deleteDirectory($directory);
            }

            return redirect()->route('scorm.index')->with('success', 'SCORM package deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete SCORM package: ' . $e->getMessage());
            return redirect()->route('scorm.index')->with('error', 'Failed to delete SCORM package: ' . $e->getMessage());
        }
    }
}
