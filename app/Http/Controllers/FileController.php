<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(
        protected FileService $fileService,
    ) {}

    public function show(string $path): StreamedResponse
    {
        return $this->fileService->preview($path);
    }
}
