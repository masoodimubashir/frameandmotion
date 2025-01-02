<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google\Service\Drive;
use Google\Client;


class FileDownloadController extends Controller
{



    private $drive;

    public function __construct()
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->refreshToken(config('services.google.client_refresh_token'));
    
        $this->drive = new Drive($client);
    }
    
    public function downloadImage(Request $request)
    {
        $request->validate([
            'driveIds' => 'required|array',
            'driveIds.*' => 'required|string'
        ]);
    
        try {
            // If only one file is selected, download directly
            if (count($request->driveIds) === 1) {
                $driveId = $request->driveIds[0];
                $file = File::where('drive_id', $driveId)->firstOrFail();
    
                // Get the file from Google Drive using the Google Client
                $response = $this->drive->files->get($driveId, [
                    'alt' => 'media'
                ]);
    
                $content = $response->getBody()->getContents();
                $ext = pathinfo($file->name, PATHINFO_EXTENSION);
    
                // Save the file to the storage and initiate download
                $filePath = '/downloads/' . $file->name . '.' . $ext;
                Storage::put($filePath, $content);
    
                return Storage::download($filePath);
            }
    
            // For multiple files, handle each file download individually
            $downloadLinks = [];
    
            foreach ($request->driveIds as $driveId) {
                $file = File::where('drive_id', $driveId)->firstOrFail();
                $ext = pathinfo($file->name, PATHINFO_EXTENSION);
    
                // Fetch the file content from Google Drive using the Google Client
                $response = $this->drive->files->get($driveId, [
                    'alt' => 'media'
                ]);
    
                $content = $response->getBody()->getContents();
                $filePath = '/downloads/' . $file->name . '.' . $ext;
    
                // Store the file locally
                Storage::put($filePath, $content);
    
                // Store download link for the file
                $downloadLinks[] = Storage::url($filePath);
            }
    
            // Return all download links as a response (to initiate download on the frontend)
            return response()->json([
                'download_links' => $downloadLinks
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Download failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
