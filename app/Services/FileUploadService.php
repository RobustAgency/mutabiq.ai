<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function uploadFile(UploadedFile $file, string $path, string $disk = 'local'): string|false
    {
        return $file->store($path, $disk);
    }

    public function deleteFile(string $filePath, string $disk = 'local'): bool
    {
        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }

        return false;
    }

    public function replaceFile(UploadedFile $newFile, ?string $oldFilePath, string $path, string $disk = 'local'): string|false
    {
        if ($oldFilePath) {
            $this->deleteFile($oldFilePath, $disk);
        }

        return $this->uploadFile($newFile, $path, $disk);
    }
}
