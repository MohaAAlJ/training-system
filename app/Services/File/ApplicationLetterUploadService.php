<?php

declare(strict_types=1);

namespace App\Services\File;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

/**
 * ApplicationLetterUploadService
 *
 * Handles file uploads for application letters with validation and storage.
 * Encapsulates file handling logic separate from the Livewire component.
 *
 * Responsibilities:
 * - Validate uploaded files
 * - Store files with proper naming
 * - Generate file previews
 * - Handle file cleanup
 */
class ApplicationLetterUploadService
{
    private const STORAGE_PATH = 'application-letters';
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'application/pdf'];
    private const MAX_FILE_SIZE_MB = 5;
    private const MAX_FILE_SIZE_BYTES = self::MAX_FILE_SIZE_MB * 1024 * 1024;

    /**
     * Validate uploaded file
     *
     * @return array{valid: bool, message: string}
     */
    public function validateFile(UploadedFile $file): array
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            return [
                'valid' => false,
                'message' => "حجم الملف يجب ألا يتجاوز " . self::MAX_FILE_SIZE_MB . " ميجابايت",
            ];
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES)) {
            return [
                'valid' => false,
                'message' => 'نوع الملف غير مدعوم. استخدم صورة (JPG/PNG) أو PDF',
            ];
        }

        return [
            'valid' => true,
            'message' => '',
        ];
    }

    /**
     * Store application letter and return path
     *
     * @return string File storage path
     */
    public function storeApplicationLetter(UploadedFile $file, int $applicationId, int $traineeId): string
    {
        try {
            $extension = $file->getClientOriginalExtension();
            $filename = "{$applicationId}_{$traineeId}.{$extension}";

            $path = $file->storeAs(self::STORAGE_PATH, $filename, 'public');

            Log::info('Application letter stored', [
                'applicationId' => $applicationId,
                'path' => $path,
            ]);

            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to store application letter', [
                'applicationId' => $applicationId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get file preview information
     *
     * @return array{type: string, url: string}
     */
    public function getFilePreview(UploadedFile $file): array
    {
        $mimeType = $file->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            return [
                'type' => 'image',
                'url' => $file->temporaryUrl(),
            ];
        }

        if ($mimeType === 'application/pdf') {
            return [
                'type' => 'pdf',
                'url' => $file->temporaryUrl(),
            ];
        }

        return [
            'type' => 'unknown',
            'url' => '',
        ];
    }

    /**
     * Delete application letter from storage
     */
    public function deleteApplicationLetter(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('Application letter deleted', ['path' => $path]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to delete application letter', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
