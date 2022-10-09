<?php

namespace App\Http\Controllers;

use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller
{
    private StorageService $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Upload new file.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function upload(Request $request, Response $response): Response
    {
        if (!$file = $this->storageService->uploadFile($request)) {
            return $response->setStatusCode(520)->setContent([
                'success' => false,
                'message' => 'The file has not been saved',
            ]);
        }
        return $response->setStatusCode(201)->setContent([
            'success' => true,
            'message' => 'File successfully uploaded',
            'file_id' => $file->id,
        ]);
    }

    /**
     * Download the file.
     *
     * @param string $id
     * @return StreamedResponse
     */
    public function download(string $id = '')
    {
        $this->validateId($id);
        $result = $this->storageService->downloadFile($id);
        if (!$result) {
            throw new NotFoundHttpException;
        }
        return $result;
    }

    /**
     * Download shared file.
     *
     * @param string $shareId
     * @return StreamedResponse
     */
    public function downloadShared(string $shareId = '')
    {
        $validator = Validator::make(['shareId' => $shareId], ['shareId' => 'required|uuid']);
        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
        $result = $this->storageService->downloadSharedFile($shareId);
        if (!$result) {
            throw new NotFoundHttpException;
        }
        return $result;
    }

    /**
     * Create file share.
     *
     * @param Response $response
     * @param string $id
     * @return Response
     */
    public function createFileShare(Response $response, string $id = '')
    {
        $this->validateId($id);
        $result = $this->storageService->createFileShare($id);
        if (!$result) {
            throw new NotFoundHttpException;
        }
        return $response->setContent([
            'success' => true,
            'url' => StorageService::getSharedLink($result->share_id),
        ]);
    }

    /**
     * Delete file share.
     *
     * @param Response $response
     * @param string $id
     * @return Response
     */
    public function deleteFileShare(Response $response, string $id = '')
    {
        $this->validateId($id);
        $result = $this->storageService->deleteFileShare($id);
        if (!$result) {
            throw new NotFoundHttpException;
        }
        return $response->setContent([
            'success' => true,
        ]);
    }

    /**
     * Rename file.
     *
     * @param Request $request
     * @param Response $response
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function rename(Request $request, Response $response, string $id = '')
    {
        $this->validateId($id);
        $result = $this->storageService->renameFile($id, $request);
        if (!$result) {
            throw new NotFoundHttpException;
        }
        return $response->setContent([
            'success' => true,
        ]);
    }

    /**
     * Remove file.
     *
     * @param Response $response
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Response $response, string $id = '')
    {
        $this->validateId($id);
        $result = $this->storageService->deleteFile($id);
        if (!$result) {
            throw new NotFoundHttpException;
        }
        return $response->setContent([
            'success' => true,
        ]);
    }

    /**
     * Validate id
     *
     * @param string $id
     * @param string $name
     * @return void
     */
    private function validateId(string $id, string $name = 'id')
    {
        $validator = Validator::make([$name => $id], [$name => 'required|integer']);
        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }
}
