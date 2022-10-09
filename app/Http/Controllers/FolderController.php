<?php

namespace App\Http\Controllers;

use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FolderController extends Controller
{
    private StorageService $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function create(Request $request, Response $response)
    {
        if (!$folder = $this->storageService->createFolder($request)) {
            return $response->setStatusCode(520)->setContent([
                'success' => false,
                'message' => 'The folder was not created',
            ]);
        }
        return $response->setStatusCode(201)->setContent([
            'success' => true,
            'message' => 'Folder created successfully',
            'id' => $folder->id,
        ]);
    }
}
