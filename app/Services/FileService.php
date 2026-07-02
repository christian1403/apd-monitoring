<?php

namespace App\Services;

use App\Infrastructure\BaseService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileService extends BaseService
{
    public const DISK_LOCAL = 'local';

    public const DISK_S3 = 's3';

    /**
     * Resolve a Storage disk instance by type.
     *
     * @param  'local'|'s3'  $disk
     */
    private function disk(string $disk = self::DISK_LOCAL): Filesystem
    {
        return Storage::disk($disk);
    }

    /**
     * Store an uploaded file on the given disk.
     *
     * @param  'local'|'s3'  $disk
     * @return string The stored file path relative to the disk root.
     */
    public function store(
        UploadedFile $file,
        string $directory = 'uploads',
        string $disk = self::DISK_LOCAL,
        string $visibility = 'private',
    ): string {
        $path = $file->store($directory, [
            'disk' => $disk,
            'visibility' => $visibility,
        ]);

        return $path;
    }

    /**
     * Check whether a file exists on the given disk.
     *
     * @param  'local'|'s3'  $disk
     */
    public function exists(string $path, string $disk = self::DISK_LOCAL): bool
    {
        return $this->disk($disk)->exists($path);
    }

    /**
     * Get the URL or temporary signed URL to access the file.
     *
     * - local disk  → returns a temporary signed URL (valid for $minutes).
     * - s3 disk     → returns a pre-signed temporary URL (valid for $minutes).
     *
     * @param  'local'|'s3'  $disk
     */
    public function getUrl(
        string $path,
        string $disk = self::DISK_LOCAL,
        int $minutes = 30,
    ): string {
        $storage = $this->disk($disk);

        if ($disk === self::DISK_S3) {
            return $storage->temporaryUrl($path, now()->addMinutes($minutes));
        }

        // For local private files, generate a temporary signed URL via Laravel's
        // built-in route signing. The file is served through a controller action
        // that calls FileService::download() or FileService::preview().
        return URL::temporarySignedRoute(
            'files.show',
            now()->addMinutes($minutes),
            ['path' => $path, 'disk' => $disk],
        );
    }

    /**
     * Stream a file as a direct download response.
     *
     * @param  'local'|'s3'  $disk
     *
     * @throws FileNotFoundException
     */
    public function download(
        string $path,
        string $disk = self::DISK_LOCAL,
        ?string $fileName = null,
    ): StreamedResponse {
        $storage = $this->disk($disk);
        $fileName = $fileName ?? basename($path);
        $mime = $storage->mimeType($path) ?: 'application/octet-stream';

        return $storage->download($path, $fileName, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    /**
     * Stream a file inline (browser preview — image, PDF, video, etc.).
     *
     * @param  'local'|'s3'  $disk
     *
     * @throws FileNotFoundException
     */
    public function preview(
        string $path,
        string $disk = self::DISK_LOCAL,
    ): StreamedResponse {
        $storage = $this->disk($disk);
        $fileName = basename($path);
        $mime = $storage->mimeType($path) ?: 'application/octet-stream';

        return response()->stream(
            fn () => fpassthru($storage->readStream($path)),
            200,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$fileName.'"',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }

    /**
     * Delete a file from the given disk.
     *
     * @param  'local'|'s3'  $disk
     */
    public function delete(string $path, string $disk = self::DISK_LOCAL): bool
    {
        return $this->disk($disk)->delete($path);
    }
}
