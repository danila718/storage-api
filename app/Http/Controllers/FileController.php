<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller
{
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
     * @param FileService $fileService
     * @return Response
     */
    public function upload(Request $request, Response $response, FileService $fileService): Response
    {
        if (!$fileService->uploadFile($request)) {
            return $response->setStatusCode(520)->setContent([
                'success' => false,
                'message' => 'The file has not been saved',
            ]);
        }
        return $response->setStatusCode(201)->setContent([
            'success' => true,
            'message' => 'File successfully uploaded',
        ]);
    }

    /**
     * Download the file.
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id)
    {
        $user = Auth::user();
        $file = File::where('id', $id)->where('created_by', $user->id)->first();
        if (!$file) {
            throw new NotFoundHttpException;
        }
        return Storage::download('files' . DIRECTORY_SEPARATOR . $user->id . DIRECTORY_SEPARATOR . $file->file_name, $file->name);
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
}
