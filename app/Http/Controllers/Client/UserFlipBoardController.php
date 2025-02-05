<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Service\PdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class UserFlipBoardController extends Controller
{

    protected $adminDrive;


    public function __construct()
    {
        try {

            // Fetch admin user
            $admin = User::where('role_name', 'admin')->firstOrFail();

            if (!$admin->google_refresh_token) {
                throw new \Exception('Admin Google refresh token is missing.');
            }

            // Initialize the Google Client
            $adminClient = new Client();
            $adminClient->setClientId(config('services.google.client_id'));
            $adminClient->setClientSecret(config('services.google.client_secret'));
            $adminClient->setAccessType('offline');


            // Set the refresh token
            $adminClient->setAccessToken($admin->google_refresh_token);

            if ($adminClient->isAccessTokenExpired()) {
                Log::info($adminClient->isAccessTokenExpired());

                // Fetch a new access token using the refresh token
                $newToken = $adminClient->fetchAccessTokenWithRefreshToken($admin->google_refresh_token);

                // Save the new token back to the database
                $admin->update([
                    'google_refresh_token' => json_encode($newToken)
                ]);

            }

            // Initialize the Google Drive service
            $this->adminDrive = new Drive($adminClient);

            Log::info('Admin Drive client initialized successfully.');
            Log::info('Admin Drive client access token: ' . $adminClient->getAccessToken()['access_token']);
            Log::info('Admin Drive client refresh token: ' . $adminClient->getAccessToken()['refresh_token']);

        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function index(Request $request)
    {
        try {

            $query = $request->user()->files()->orderBy('created_at');

            if ($request->filled(['start_date', 'end_date'])) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            $files = $query->paginate(10);

            $files->getCollection()->transform(function ($file) {
                try {
                    $driveFile = $this->adminDrive->files->get($file->drive_id, [
                        'fields' => 'id, name, webContentLink, thumbnailLink',
                        'supportsAllDrives' => true
                    ]);

                    return array_merge($file->toArray(), [
                        'image_url' => $driveFile->webContentLink,
                        'thumbnail' => $driveFile->thumbnailLink
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to fetch file from Google Drive.', [
                        'file_id' => $file->drive_id,
                        'error' => $e->getMessage()
                    ]);
                    return $file;
                }
            });


            return response()->json($files);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch files',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try {


            // Validate request
            $request->validate([
                'files' => 'required|array',
                'files.*.drive_id' => 'required|string'
            ]);

            $files = $request->input('files');
            $downloadedFiles = [];
            $errors = [];

            $pdfStoragePath = storage_path('app/public/pdf');
            $tempStoragePath = storage_path('app/public/drive_files');

            // Clear directories
            foreach ([$pdfStoragePath, $tempStoragePath] as $path) {
                if (File::exists($path)) {
                    File::cleanDirectory($path);
                } else {
                    File::makeDirectory($path, 0755, true);
                }
            }

            // Initialize PDF service
            $pdf = new PdfService();
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(true, 10);

            $imageWidth = 60;
            $imageHeight = 60;
            $margin = 5;
            $imagesPerRow = 3;

            $xPos = 10;
            $yPos = 40;
            $imageCount = 0;

            // Process each file
            foreach ($files as $file) {
                try {
                    // Extract the actual Drive ID from the URL
                    $driveId = preg_match('/drive-storage\/(.+?)=/', $file['drive_id'], $matches)
                        ? $matches[1]
                        : $file['drive_id'];

                    // Get file metadata with proper ID
                    $driveFile = $this->adminDrive->files->get($driveId, [
                        'fields' => 'id, name, mimeType, size',
                        'supportsAllDrives' => true
                    ]);

                    if (!$driveFile) {
                        throw new \Exception("Failed to get file metadata");
                    }

                    // Validate mime type (only image files)
                    if (!str_starts_with($driveFile->getMimeType(), 'image/')) {
                        throw new \Exception("File is not an image: " . $driveFile->getMimeType());
                    }

                    // Download file content
                    $response = $this->adminDrive->files->get($driveId, [
                        'alt' => 'media',
                        'supportsAllDrives' => true
                    ]);

                    $content = $response->getBody()->getContents();

                    if (empty($content)) {
                        throw new \Exception("Downloaded file content is empty");
                    }

                    // Generate file name and path
                    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $driveFile->getName());
                    $filePath = $tempStoragePath . '/' . $fileName;

                    // Save the file locally
                    if (File::put($filePath, $content) === false) {
                        throw new \Exception("Failed to write file to disk");
                    }

                    // Verify file exists
                    if (!File::exists($filePath) || filesize($filePath) === 0) {
                        throw new \Exception("Saved file is invalid or empty");
                    }

                    // Add file details to the array
                    $downloadedFiles[] = [
                        'original_id' => $driveId,
                        'name' => $fileName,
                        'path' => $filePath
                    ];
                } catch (\Exception $e) {
                    // Handle errors for individual files
                    $errors[] = $e->getMessage();
                }
            }
            // If no files were processed successfully, throw an exception
            if (empty($downloadedFiles)) {
                throw new \Exception("No files were successfully processed");
            }

            // Add images to the PDF
            foreach ($downloadedFiles as $file) {
                // Check if we need a new page due to space limitations
                if ($yPos + $imageHeight > $pdf->GetPageHeight()) {
                    $pdf->AddPage();  // Add a new page if necessary
                    $xPos = 10;  // Reset X position
                    $yPos = 10;  // Reset Y position
                }

                // Add image to the PDF (scaled to fit within the margins)
                $pdf->Image($file['path'], $xPos, $yPos, $imageWidth, $imageHeight);

                // Update image count and position for the next image
                $imageCount++;
                if ($imageCount % $imagesPerRow == 0) {
                    // After 3 images, reset X to the left and move Y down to the next row
                    $xPos = 10;
                    $yPos += $imageHeight + $margin;
                } else {
                    // Move X to the right for the next image
                    $xPos += $imageWidth + $margin;
                }
            }

            // Generate PDF with a unique filename
            $pdfFilename = 'images_report_' . time() . '.pdf';
            $pdfPath = $pdfStoragePath . '/' . $pdfFilename;

            // Output the PDF to a file
            $pdf->Output('F', $pdfPath);

            // Verify PDF was created successfully
            if (!File::exists($pdfPath) || filesize($pdfPath) === 0) {
                throw new \Exception("Failed to generate valid PDF file");
            }

            // Clean up temporary files
            foreach ($downloadedFiles as $file) {
                File::delete($file['path']);
            }

            // Return success response with download URL
            return response()->json([
                'success' => true,
                'message' => 'PDF generated successfully',
                'download_url' => url("storage/pdf/{$pdfFilename}"),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $errors ?? []
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
