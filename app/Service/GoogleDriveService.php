<?php

namespace App\Service;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;


class GoogleDriveService
{
    private $client;
    private $driveService;

    public function __construct()
    {
        $this->initializeGoogleClient();
    }

    private function initializeGoogleClient()
    {
        if (session()->has('google_token')) {
            $this->client = new Google_Client();
            $this->client->setAccessToken(session('google_token'));
            $this->driveService = new Google_Service_Drive($this->client);
        }
    }

    public function searchFolders($query)
    {
        $response = $this->driveService->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
        ]);

        return $response->files[0] ?? null;
    }

    public function createFolder($name, $parentId = null)
    {
        $folder = new Google_Service_Drive_DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        if ($parentId) {
            $folder->setParents([$parentId]);
        }

        return $this->driveService->files->create($folder, ['fields' => 'id, name']);
    }

    public function uploadFile($name, $data, $mimeType, $parentId = null)
    {
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $name,
            'parents' => $parentId ? [$parentId] : [],
        ]);

        return $this->driveService->files->create(
            $fileMetadata,
            [
                'data' => $data,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink',
            ]
        );
    }

    public function deleteFile($fileId)
    {
        $this->driveService->files->delete($fileId);
    }

    public function getFileDownloadLink($fileId)
    {
        $file = $this->driveService->files->get($fileId, ['fields' => 'webContentLink']);
        return $file->webContentLink ?? null;
    }
}
