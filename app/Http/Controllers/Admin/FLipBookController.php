<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client as ModelsClient;
use App\Models\File;
use App\Models\User;
use App\Service\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            
            $users = User::whereIn('id', File::select('user_id')->distinct())
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            if ($request->ajax()) {
                $query = File::query();

                if ($request->has('user_id') && $request->user_id) {
                    $query->where('user_id', $request->user_id);
                }

                $files = $query->with('user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(12);

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

            // Delete the old file
            $this->driveService->deleteFile($file->drive_id);
            $file->delete();

            $newFile = $request->file('image');
            $newFileName = time() . '_' . $newFile->getClientOriginalName();

            // Upload the new file
            $uploadedFile = $this->driveService->uploadFile(
                $newFileName,
                file_get_contents($newFile->getRealPath()),
                $newFile->getMimeType(),
                $file->folder_id
            );

            $file->update([
                'name' => $newFileName,
                'drive_id' => $uploadedFile->id,
                'drive_link' => $uploadedFile->webViewLink,
                'mime_type' => $newFile->getMimeType(),
            ]);

            return response()->json(['message' => 'File updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'images' => ['required', 'string'],
        ]);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => $validation->errors()->first()], 422);
        }

        try {
            $images = json_decode($request->images, true);

            if (!is_array($images) || empty($images)) {
                return response()->json(['success' => false, 'message' => 'No images provided for deletion'], 422);
            }

            DB::beginTransaction();

            foreach ($images as $image) {
                $file = File::where('id', $image['id'])->where('drive_id', $image['drive_id'])->first();

                if (!$file) {
                    continue;
                }

                $this->driveService->deleteFile($file->drive_id);
                $file->delete();
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Files deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
