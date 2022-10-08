<?php

namespace App\Services;

use App\Models\File as FileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;

class FileService
{
    /**
     * @param Request $request
     * @return FileModel|null
     */
    public function uploadFile(Request $request): ?FileModel
    {
        $preValidator = Validator::make($request->all(), [
            'dir_id' => 'integer',
            'file' => [
                'required',
                File::default()
                    ->max(20 * 1024),
            ]
        ]);
        if ($preValidator->fails()) {
            throw ValidationException::withMessages($preValidator->errors()->toArray());
        }

        $dirId = $request->get('dir_id');
        $file = $request->file('file');
        $user = Auth::user();
        $fileValidator = Validator::make(
            [
                'extension' => $file->extension() ?: $file->guessClientExtension(),
                'name' => $file->getClientOriginalName(),
            ],
            [
                'extension' => 'not_in:php',
                'name' => [
                    'required',
                    Rule::unique('files')->where('created_by', $user->id)->where('dir_id', $dirId)
                ],
            ]
        );
        if ($fileValidator->fails()) {
            throw ValidationException::withMessages($fileValidator->errors()->toArray());
        }

        $filePath = 'files/' . $user->id;
        $model = new FileModel;
        $model->name = $file->getClientOriginalName();
        $model->file_name = $file->hashName();
        $model->file_size = $file->getSize();
        $model->dir_id = $dirId;

        if (!$model->save() || !$file->store($filePath)) {
            Storage::delete($filePath . DIRECTORY_SEPARATOR . $file->hashName());
            return null;
        }

        return $model;
    }
}
