<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Google\Service\Drive;
use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FLipBookController extends Controller
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

            $validation = Validator::make($request->all(), [

                'user_id' => ['required', 'exists:users,id'],
                'images' => ['required', 'array', 'min:1'],
                'images.*' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg',
                ]
            ]);

            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 422);
            }


            $userId = $request->user_id;

            // Search for the folder with the current user_id
            $query = sprintf(
                "name = '%s' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                $userId
            );

            $existingFolders = $this->drive->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)'
            ]);

            $driveFolderId = null;

            if (count($existingFolders->files) > 0) {
                // Folder exists, get its ID
                $driveFolderId = $existingFolders->files[0]->id;
            } else {
                // Folder doesn't exist, create a new one
                $newFolder = $this->drive->files->create(
                    new Drive\DriveFile([
                        'name' => $userId,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'parents' => [config('services.google.folder_id')]
                    ]),
                    ['fields' => 'id, name']
                );

                $driveFolderId = $newFolder->id;
            }

            // Upload images to the folder
            foreach ($request->file('images') as $file) {
                
                $fileName = time() . '_' . $file->getClientOriginalName();

                $uploadedFile = $this->drive->files->create(
                    new Drive\DriveFile([
                        'name' => $fileName,
                        'parents' => [$driveFolderId]
                    ]),
                    [
                        'data' => file_get_contents($file->getRealPath()),
                        'mimeType' => $file->getMimeType(),
                        'uploadType' => 'multipart',
                        'fields' => 'id, name, webViewLink'
                    ]
                );

                // Save file metadata to the database
                File::create([
                    'name' => $fileName,
                    'drive_id' => $uploadedFile->id,
                    'folder_id' => $driveFolderId,
                    'user_id' => $userId,
                    'drive_link' => $uploadedFile->webViewLink,
                    'mime_type' => $file->getMimeType()
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
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



        try {
            // Retrieve the file record from the database
            $file = File::findOrFail($id);

            // Validate the uploaded image
            $validation = Validator::make($request->all(), [
                'user_id' => ['required', 'exists:users,id'],
                'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'min:1']
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'errors' => $validation->errors()
                ], 422);
            }

            // Get the folder ID of the existing image
            $userFolderId = $file->folder_id;

            if (!$userFolderId) {
                return response()->json(['error' => 'Folder ID not found for the file'], 404);
            }

            // Validate that the folder ID exists directly
            try {
                $folder = $this->drive->files->get($userFolderId, ['fields' => 'id']);
            } catch (\Google\Service\Exception $e) {
                return response()->json(['error' => 'User folder not found on Google Drive'], 404);
            }

            $this->drive->files->delete($file->drive_id);

            $file->delete();

            // Store the new image temporarily
            $newFile = $request->file('image');

            $newFileName = time() . '_' . $newFile->getClientOriginalName();

            $newFilePath = $newFile->storeAs('temp', $newFileName);

            // Upload the new image to the same folder in Google Drive
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $newFileName,
                'parents' => [$userFolderId] // Use the same folder ID
            ]);

            $content = Storage::get('temp/' . $newFileName);

            $driveFile = $this->drive->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => Storage::mimeType('temp/' . $newFileName),
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]);

            // Save the new image details in the database
            File::create([
                'name' => $newFileName,
                'drive_id' => $driveFile->id,
                'folder_id' => $userFolderId,
                'user_id' => $request->user_id,
                'drive_link' => $driveFile->webViewLink,
                'mime_type' => Storage::mimeType('temp/' . $newFileName),
                'view_link' => $driveFile->webViewLink
            ]);

            // Clean up the temporary file
            Storage::delete('temp/' . $newFileName);

            return response()->json(['message' => 'Image updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to update image: ' . $e->getMessage()], 500);
        }
    }





    public function destroy(Request $request)
    {
        try {
            // Decode JSON input if it's a string
            $images = json_decode($request->input('images'), true);

            if (!is_array($images)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid images input. Expected an array.',
                ], 400);
            }

            $folderId = null;

            foreach ($images as $image) {
                $file = File::find($image['id']);

                if ($file) {
                    $folderId = $file->folder_id;

                    // Delete the file from Google Drive
                    $this->drive->files->delete($image['drive_id']);

                    // Delete the record from the database
                    $file->delete();
                }
            }

            // Check if the folder is empty in Google Drive and delete it if so
            if ($folderId) {
                // Query the folder contents in Google Drive
                $query = "'{$folderId}' in parents and trashed = false";
                $response = $this->drive->files->listFiles([
                    'q' => $query,
                    'fields' => 'files(id)',
                ]);

                $folderContents = $response->getFiles();

                // If folder is empty, delete it
                if (empty($folderContents)) {
                    $this->drive->files->delete($folderId);

                    // Delete the folder record from the database
                    $folder = Folder::find($folderId); // Adjust your model if necessary
                    if ($folder) {
                        $folder->delete();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Images deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting images: ' . $e->getMessage(),
            ], 500);
        }
    }
}
