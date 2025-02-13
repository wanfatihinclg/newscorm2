<?php

namespace App\Services;

use App\Models\Scorm;
use App\Models\ScormSco;
use App\Models\ScormScoData;
use App\Models\ScormElement;
use App\Models\ScormScoValue;
use App\Models\ScormAttempt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use DOMDocument;
use Exception;

class ScormService
{
    protected $storageBasePath;

    public function __construct()
    {
        $this->storageBasePath = '';
    }

    public function uploadPackage(UploadedFile $file, $courseId)
    {
        try {
            // Generate unique directory name for this SCORM package
            $packageDir = uniqid('scorm_');
            $packagePath = $packageDir;
            
            Log::info('Starting SCORM upload', [
                'originalName' => $file->getClientOriginalName(),
                'packageDir' => $packageDir,
                'packagePath' => $packagePath
            ]);

            // Create public directory if it doesn't exist
            $publicPath = public_path('scorm_files');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0775, true);
            }

            $extractPath = $publicPath . '/' . $packagePath;
            if (!file_exists($extractPath)) {
                mkdir($extractPath, 0775, true);
            }

            Log::info('Extract path', ['path' => $extractPath]);

            // Get file hashes before extraction
            $md5Hash = md5_file($file->getRealPath());
            $sha1Hash = sha1_file($file->getRealPath());

            // Extract the ZIP file
            $zip = new ZipArchive;
            if ($zip->open($file->getRealPath()) !== true) {
                throw new Exception('Failed to open ZIP file');
            }

            // Extract the contents
            if (!$zip->extractTo($extractPath)) {
                throw new Exception('Failed to extract ZIP file');
            }
            $zip->close();

            // Find and parse the manifest file
            $manifestPath = $this->findManifestFile($extractPath);
            if (!$manifestPath) {
                throw new Exception('imsmanifest.xml not found in SCORM package');
            }

            // Parse the manifest
            $manifest = new DOMDocument();
            if (!$manifest->load($manifestPath)) {
                throw new Exception('Failed to parse imsmanifest.xml');
            }

            // Create SCORM record
            $scorm = new Scorm();
            $scorm->course_id = $courseId;
            $scorm->version = $this->getScormVersion($manifest);
            $scorm->reference = $packagePath;
            $scorm->name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $scorm->scorm_type = 'local';
            $scorm->md5_hash = $md5Hash;
            $scorm->sha1_hash = $sha1Hash;
            $scorm->max_grade = 100;
            $scorm->max_attempt = 1;
            $scorm->save();

            // Process SCOs
            $this->processScos($manifest, $scorm);

            Log::info('SCORM package processed successfully', [
                'scorm_id' => $scorm->id,
                'reference' => $scorm->reference
            ]);

            return $scorm;

        } catch (Exception $e) {
            // Clean up the extracted files if something went wrong
            if (isset($extractPath) && file_exists($extractPath)) {
                $this->rrmdir($extractPath);
            }
            
            Log::error('SCORM upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    protected function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object))
                        $this->rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            rmdir($dir);
        }
    }

    protected function findManifestFile($directory)
    {
        $manifestPath = $directory . '/imsmanifest.xml';
        return file_exists($manifestPath) ? $manifestPath : null;
    }

    protected function getScormVersion(DOMDocument $manifest)
    {
        $schemaVersion = $manifest->getElementsByTagName('schemaversion')->item(0);
        return $schemaVersion ? $schemaVersion->nodeValue : '1.2';
    }

    protected function processScos(DOMDocument $manifest, Scorm $scorm)
    {
        $organizations = $manifest->getElementsByTagName('organizations')->item(0);
        if (!$organizations) {
            throw new Exception('Invalid SCORM package: no organizations found');
        }

        // First, build a map of resources
        $resources = [];
        $resourceElements = $manifest->getElementsByTagName('resource');
        foreach ($resourceElements as $resource) {
            $identifier = $resource->getAttribute('identifier');
            $href = $resource->getAttribute('href');
            $scormType = $resource->getAttribute('adlcp:scormtype') ?? 'asset';
            $resources[$identifier] = [
                'href' => $href,
                'scormType' => $scormType
            ];
        }
        
        // Process organizations and their items
        foreach ($organizations->getElementsByTagName('organization') as $org) {
            $orgIdentifier = $org->getAttribute('identifier');
            
            // Process items recursively
            $this->processItems($org->getElementsByTagName('item'), $scorm, $orgIdentifier, '', $resources);
        }
    }

    protected function processItems($items, Scorm $scorm, $organization, $parent = null, $resources = [], &$sortOrder = 0)
    {
        foreach ($items as $item) {
            $identifier = $item->getAttribute('identifier');
            $title = $item->getElementsByTagName('title')->item(0)?->nodeValue ?? $identifier;
            $launch = '';
            $parameters = '';
            $scormType = 'asset';
            
            // Get identifierref to find the resource
            $identifierref = $item->getAttribute('identifierref');
            if ($identifierref && isset($resources[$identifierref])) {
                $resource = $resources[$identifierref];
                // Split URL and parameters
                $url = $resource['href'];
                
                if (strpos($url, '?') !== false) {
                    list($url, $parameters) = explode('?', $url, 2);
                }
                
                $launch = $url;
                $scormType = $resource['scormType'];
            }

            // Create SCO record
            $sco = new ScormSco();
            $sco->scorm_id = $scorm->id;
            $sco->manifest = $organization;
            $sco->organization = $organization;
            $sco->parent = $parent;
            $sco->identifier = $identifier;
            $sco->launch = $launch;
            $sco->scorm_type = $scormType;
            $sco->title = $title;
            $sco->parameters = $parameters;
            $sco->sort_order = $sortOrder++;
            $sco->save();

            // Process child items recursively
            if ($item->getElementsByTagName('item')->length > 0) {
                $this->processItems(
                    $item->getElementsByTagName('item'),
                    $scorm,
                    $organization,
                    $identifier,
                    $resources,
                    $sortOrder
                );
            }
        }
    }

    public function saveAttempt($userId, $scormId, $scoId, $data)
    {
        $attempt = ScormAttempt::firstOrCreate([
            'user_id' => $userId,
            'scorm_id' => $scormId,
            'attempt' => $this->getNextAttemptNumber($userId, $scormId),
        ]);

        foreach ($data as $elementName => $value) {
            $element = ScormElement::firstOrCreate(['element' => $elementName]);

            ScormScoValue::create([
                'sco_id' => $scoId,
                'attempt_id' => $attempt->id,
                'element_id' => $element->id,
                'value' => $value,
            ]);
        }

        return $attempt;
    }

    protected function getNextAttemptNumber($userId, $scormId)
    {
        $lastAttempt = ScormAttempt::where('user_id', $userId)
            ->where('scorm_id', $scormId)
            ->orderBy('attempt', 'desc')
            ->first();

        return $lastAttempt ? $lastAttempt->attempt + 1 : 1;
    }

    public function getAttemptData($attemptId, $scoId = null)
    {
        $attempt = ScormAttempt::findOrFail($attemptId);
        
        // Initialize default data structure
        $data = [
            'cmi.core.student_id' => '',  // Will be filled if user system is implemented
            'cmi.core.student_name' => '', // Will be filled if user system is implemented
            'cmi.core.lesson_location' => $attempt->last_location ?? '',
            'cmi.core.lesson_status' => $attempt->status ?? 'not attempted',
            'cmi.core.score.raw' => $attempt->score ?? '',
            'cmi.core.total_time' => $attempt->total_time ?? '0000:00:00',
            'cmi.suspend_data' => $attempt->suspend_data ?? '',
            'cmi.launch_data' => '',
            // SCORM 2004 equivalents
            'cmi.location' => $attempt->last_location ?? '',
            'cmi.completion_status' => $attempt->status ?? 'not attempted',
            'cmi.score.raw' => $attempt->score ?? '',
            'cmi.total_time' => $attempt->total_time ?? '0000:00:00'
        ];

        // Get any additional stored values
        $query = $attempt->values();
        if ($scoId) {
            $query->where('scorm_sco_id', $scoId);
        }

        foreach ($query->get() as $value) {
            $data[$value->element] = $value->value;
        }

        return $data;
    }
}
