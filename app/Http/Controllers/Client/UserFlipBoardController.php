<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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


    private $drive;


    public function __construct()
    {

        try {
            $client = new Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->refreshToken(config('services.google.client_refresh_token'));

            // Add scopes explicitly
            $client->addScope('https://www.googleapis.com/auth/drive.readonly');

            // Set access type to offline to ensure refresh token works
            $client->setAccessType('offline');

            $this->drive = new Drive($client);

            Log::info('Google Drive client initialized successfully');
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Drive client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $user = Auth::user();

        $query = $user->files()->orderBy('created_at');

        if ($request->filled(['start_date', 'end_date'])) {

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $files = $query->paginate(10);
        return response()->json($files);
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

            $imageWidth = 60;  // Set your desired width for the images
            $imageHeight = 60; // Set your desired height for the images
            $margin = 5;       // Margin between images
            $imagesPerRow = 3; // Number of images per row

            $xPos = 10;  // Starting X position
            $yPos = 40;  // Starting Y position
            $imageCount = 0;

            // Process each file
            foreach ($files as $file) {
                try {
                    // Get file metadata with error checking
                    $driveFile = $this->drive->files->get($file['drive_id'], [
                        'fields' => 'id, name, mimeType, size'
                    ]);

                    if (!$driveFile) {
                        throw new \Exception("Failed to get file metadata");
                    }

                    // Validate mime type (only image files)
                    if (!str_starts_with($driveFile->getMimeType(), 'image/')) {
                        throw new \Exception("File is not an image: " . $driveFile->getMimeType());
                    }

                    // Download the file content
                    $response = $this->drive->files->get($file['drive_id'], ['alt' => 'media']);
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
                        'original_id' => $file['drive_id'],
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
