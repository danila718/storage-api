<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        if (!$file = $this->fileService->uploadFile($request)) {
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
        $result = $this->fileService->downloadFile($id);
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
        $result = $this->fileService->downloadSharedFile($shareId);
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
        $result = $this->fileService->createFileShare($id);
        if (!$result) {
            throw new NotFoundHttpException;
        }
        return $response->setContent([
            'success' => true,
            'url' => url('download/' . $result->share_id),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        //
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
