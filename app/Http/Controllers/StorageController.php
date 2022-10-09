<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileResource;
use App\Http\Resources\FolderResource;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StorageController extends Controller
{
    private StorageService $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Get list of folders and files
     *
     * @param Request $request
     * @return array
     */
    public function list(Request $request)
    {
        return [
            'folders' => FolderResource::collection($this->storageService->getFolders($request)),
            'files' => FileResource::collection($this->storageService->getFiles($request)),
        ];
    }

    /**
     * Get the total size of files on a drive or folder.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function totalSize(Request $request, Response $response)
    {
        return $response->setContent([
            'success' => true,
            'size' => $this->storageService->totalSize($request),
        ]);
    }
}
