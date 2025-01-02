<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
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

    public function index()
    {
        $files = File::paginate(10);
        $users = User::where('role_name', 'client')->get();
        return response()->json([
            'files' => $files,
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {

        try {

            $driveFolder = $this->drive->files->create(
                new Drive\DriveFile([
                    'name' => $request->user_id,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'parents' => [config('services.google.folder_id')]
                ]),
                ['fields' => 'id, name, webViewLink']
            );

            foreach ($request->file('images') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();

                $uploadedFile = $this->drive->files->create(
                    new Drive\DriveFile([
                        'name' => $fileName,
                        'parents' => [$driveFolder->id]
                    ]),
                    [
                        'data' => file_get_contents($file->getRealPath()),
                        'mimeType' => $file->getMimeType(),
                        'uploadType' => 'multipart',
                        'fields' => 'id, name, webViewLink'
                    ]
                );

                File::create([
                    'name' => $fileName,
                    'drive_id' => $uploadedFile->id,
                    'folder_id' => $driveFolder->id,
                    'user_id' => $request->user_id,
                    'drive_link' => $uploadedFile->webViewLink,
                    'mime_type' => $file->getMimeType()
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Upload failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $image = File::findOrFail($id);
        $file = $this->drive->files->get($image->drive_id, ['fields' => 'webContentLink']);
        return response()->json(['download_url' => $file->webContentLink]);
    }


    public function update(Request $request, $id)
    {

        dd($request->all());

        try {
            $file = File::findOrFail($id);

            // Delete the old file from Google Drive
            $this->drive->files->delete($file->drive_id);

            $file->delete();

            $request->validate([
                'image' => 'required|image|max:2048'
            ]);

            // Store the new image temporarily
            $newFile = $request->file('image');
            $newFileName = time() . '_' . $newFile->getClientOriginalName();
            $newFilePath = $newFile->storeAs('temp', $newFileName);

            // Upload to Google Drive
            $fileMetadata = new Drive\DriveFile([
                'name' => $newFileName,
                'parents' => [config('services.google.folder_id')]  // Use appropriate folder ID
            ]);

            $content = Storage::get('temp/' . $newFileName);

            $driveFile = $this->drive->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => Storage::mimeType('temp/' . $newFileName),
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]);

            // Save the new file details to the database
            $newImage = File::create([
                'name' => $newFileName,
                'drive_id' => $driveFile->id,
                'mime_type' => Storage::mimeType('temp/' . $newFileName),
                'view_link' => $driveFile->webViewLink
            ]);

            // Clean up the temporary file
            Storage::delete('temp/' . $newFileName);

            return response()->json(['message' => 'Image updated successfully']);
        } catch (\Exception $e) {
            Log::error('Error replacing file', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Unable to update image'], 500);
        }
    }


    public function destroy($id)
    {
        try {


            $images = $request->input('images');

            $folderId = null;

            foreach ($images as $image) {

                $file = File::find($image['id']);

                if ($file) {

                    $folderId = $file->folder_id;

                    $this->drive->files->delete($image['drive_id']);

                    $file->delete();
                }
            }

            if ($folderId) {

                $remainingFiles = File::where('folder_id', $folderId)->count();

                if ($remainingFiles === 0) {

                    $folder = File::find($folderId);

                    if ($folder) {
                        $folder->delete();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Images deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting images: ' . $e->getMessage(),
            ], 500);
        }
    }
}




/**
 * Store a newly created resource in storage.
 */
    // public function store(Request $request)
    // {

    //     // $request->validate([
    //     //     'image' => 'file|required',
    //     //     'file_name' => 'required'
    //     // ]);

    //     $access_token = $this->token();

    //     $name = $request->image->getClientOriginalName();

    //     $path = $request->image->getRealPath();

    //     // $response = Http::withToken($access_token)->attach('data', file_get_contents($path), $name)
    //     //     ->post(
    //     //         'https://www.googleapis.com/upload/drive/v3/files',
    //     //         [
    //     //             'name' => $name,
    //     //         ],
    //     //         [
    //     //             'Content-Type' => 'application/octet-stream',
    //     //             'parents' => [config('services.google.folder_id')],

    //     //         ]
    //     //     );


    //     $files = [
    //         ['path' => 'path/to/file1.txt', 'name' => 'File1.txt'],
    //         ['path' => 'path/to/file2.pdf', 'name' => 'File2.pdf'],
    //         ['path' => 'path/to/file3.jpg', 'name' => 'File3.jpg'],
    //     ];

    //     foreach ($files as $file) {
    //         $path = $file['path'];
    //         $name = $file['name'];

    //         if (file_exists($path)) {
    //             $response = Http::withToken($access_token)
    //                 ->attach('data', file_get_contents($path), $name)
    //                 ->post(
    //                     'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart',
    //                     [
    //                         'name' => $name,
    //                         // 'parents' => [$folderId], // Specify the folder ID
    //                     ]
    //                 );

    //             if ($response->successful()) {
    //                 echo "Uploaded: {$name}\n";
    //             } else {
    //                 echo "Failed to upload: {$name}. Error: " . $response->body() . "\n";
    //             }
    //         } else {
    //             echo "File not found: {$path}\n";
    //         }
    //     }


    //     if ($response->successful()) {

    //         $file_id = json_decode($response->body())->id;
    //         $uploadedFile = new File;
    //         $uploadedFile->file_name = $request->file_name;
    //         $uploadedFile->file_id = $file_id;
    //         $uploadedFile->name = $name;
    //         $uploadedFile->save();
    //         return response('file Uploaded To Drive');
    //     } else {
    //         return response('Failed To  Uploaded To Drive');
    //     }
    // }
