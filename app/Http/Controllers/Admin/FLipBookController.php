<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use App\Service\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FLipBookController extends Controller
{
    private $driveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->driveService = $googleDriveService;
    }

    public function index(Request $request)
    {
        try {

            $users = User::where('role_name', 'client')->get();

            if ($request->ajax()) {

                $query = File::query();

                if ($request->has('user_id') && $request->user_id) {
                    $query->where('user_id', $request->user_id);
                }

                $files = $query->with('user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

                return response()->json([
                    'success' => true,
                    'users' => $users,
                    'files' => $files,
                ]);
            }
        } catch (\Exception $e) {
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 500)
                : back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {


        $validation = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);

        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        try {

            $userId = $request->user_id;

            $parentFolderId = '17FPmHjZCS0512fTOx99gqYOUIAAjio8P';

            // Find or create user folder
            $query = sprintf("name = '%s' and mimeType = 'application/vnd.google-apps.folder' and trashed = false", $userId);
            
            $folder = $this->driveService->searchFolders($query) ?? $this->driveService->createFolder($userId, $parentFolderId);

            foreach ($request->file('images') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $uploadedFile = $this->driveService->uploadFile(
                    $fileName,
                    file_get_contents($file->getRealPath()),
                    $file->getMimeType(),
                    $folder->id
                );

                File::create([
                    'name' => $fileName,
                    'drive_id' => $uploadedFile->id,
                    'folder_id' => $folder->id,
                    'user_id' => $userId,
                    'drive_link' => $uploadedFile->webViewLink,
                    'mime_type' => $file->getMimeType(),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Files uploaded successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);

        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        try {
            $file = File::findOrFail($id);

            // Delete the old file from Google Drive
            $this->driveService->deleteFile($file->drive_id);

            // Upload new file
            $newFile = $request->file('image');
            $newFileName = time() . '_' . $newFile->getClientOriginalName();

            $uploadedFile = $this->driveService->uploadFile(
                $newFileName,
                file_get_contents($newFile->getRealPath()),
                $newFile->getMimeType(),
                $file->folder_id
            );

            // Update the file record in the database
            $file->update([
                'name' => $newFileName,
                'drive_id' => $uploadedFile->id,
                'drive_link' => $uploadedFile->webViewLink,
                'mime_type' => $newFile->getMimeType(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File updated successfully',
                'data' => $file
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request)
    {
        try {


            $request->validate([
                'images' => 'required|string'
            ]);

            $imagesData = json_decode($request->images, true);


            if (empty($imagesData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No images selected for deletion'
                ], 400);
            }

            $processedFolders = [];
            $deletedCount = 0;

            foreach ($imagesData as $image) {
                $file = File::find($image['id']);

                if (!$file) {
                    continue;
                }

                try {
                    // Delete file from Google Drive using the service
                    $this->driveService->deleteFile($image['drive_id']);

                    // Track the folder for potential cleanup
                    if (!in_array($file->folder_id, $processedFolders)) {
                        $processedFolders[] = $file->folder_id;
                    }

                    // Delete database record
                    $file->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to delete file:', [
                        'file_id' => $image['id'],
                        'drive_id' => $image['drive_id'],
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other deletions even if one fails
                }
            }

            // Clean up empty folders
            foreach ($processedFolders as $folderId) {
                $remainingFiles = File::where('folder_id', $folderId)->count();

                if ($remainingFiles === 0) {
                    try {
                        // Delete empty folder from Google Drive
                        $this->driveService->deleteFile($folderId);
                    } catch (\Exception $e) {
                        Log::error('Failed to delete folder:', [
                            'folder_id' => $folderId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            $message = $deletedCount > 0
                ? "Successfully deleted {$deletedCount} image(s)"
                : "No images were deleted";

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk deletion failed:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process deletion request: ' . $e->getMessage()
            ], 500);
        }
    }
}
