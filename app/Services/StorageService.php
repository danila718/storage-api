<?php

namespace App\Services;

use App\Models\File as FileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageService
{
    private const DEFAULT_TOTAL_USER_SPACE = 10 * 1024 * 1024;

    private int $totalUserSpace;

    public function __construct()
    {
        $this->totalUserSpace = config('services.storage.totalUserSpace', self::DEFAULT_TOTAL_USER_SPACE);
    }

    /**
     * Get the total size of files on a drive or folder.
     *
     * @param int $dirId
     * @return float
     */
    public function totalFilesSize(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'dir_id' => [
                'integer',
                Rule::exists('files')
                    ->where('created_by', $user->id),
            ]
        ]);
        return self::formatSize(FileModel::totalUserFilesSize($user->id, $request->get('dir_id')));
    }

    /**
     * Upload file
     *
     * @param Request $request
     * @return FileModel|null
     */
    public function uploadFile(Request $request): ?FileModel
    {
        $user = Auth::user();
        $request->validate([
            'file' => [
                'required',
                File::default()
                    ->max(20 * 1024),
            ],
            'dir_id' => [
                'sometimes',
                'integer',
                Rule::exists('files')
                    ->where('created_by', $user->id),
            ],
        ]);

        $dirId = $request->get('dir_id');
        $file = $request->file('file');
        $fileSize = $file->getSize();
        $fileValidator = Validator::make(
            [
                'extension' => $file->extension() ?: $file->guessClientExtension(),
                'name' => $file->getClientOriginalName(),
                'total_size' => $fileSize + FileModel::totalUserFilesSize($user->id),
            ],
            [
                'extension' => 'not_in:php',
                'name' => [
                    'required',
                    Rule::unique('files')
                        ->where('created_by', $user->id)
                        ->where('dir_id', $dirId),
                ],
                'total_size' => 'numeric|max:' . $this->totalUserSpace,
            ],
            [
                'total_size' => 'The total disk space must not be greater than '
                    . self::formatSize($this->totalUserSpace) . ' MB' ,
            ]
        );
        if ($fileValidator->fails()) {
            throw ValidationException::withMessages($fileValidator->errors()->toArray());
        }

        $filePath = 'files/' . $user->id;
        $model = new FileModel;
        $model->name = $file->getClientOriginalName();
        $model->file_name = $file->hashName();
        $model->file_size = $fileSize;
        $model->dir_id = $dirId;

        if (!$model->save() || !$file->store($filePath)) {
            Storage::delete($filePath . DIRECTORY_SEPARATOR . $file->hashName());
            return null;
        }

        return $model;
    }

    /**
     * Rename file.
     *
     * @param string $id
     * @param Request $request
     * @return FileModel|null
     */
    public function renameFile(string $id, Request $request): ?FileModel
    {
        $user = Auth::user();
        $file = FileModel::where('id', $id)->where('created_by', $user->id)->first();
        if (!$file) {
            return null;
        }

        $request->validate([
            'name' => 'required|string'
        ]);

        $fileName = $request->get('name');
        $nameValidator = Validator::make(
            [
                'name' => $fileName,
                'extension' => strstr($fileName, '.'),
            ],
            [
                'name' => [
                    'required',
                    Rule::unique('files')
                        ->whereNot('id', $file->id)
                        ->where('created_by', $user->id)
                        ->where('dir_id', $file->dir_id),
                ],
                'extension' => 'not_in:.php',
            ]
        );
        if ($nameValidator->fails()) {
            throw ValidationException::withMessages($nameValidator->errors()->toArray());
        }
        $file->name = $fileName;
        if (!$file->save()) {
            return null;
        }
        return $file;
    }

    /**
     * Delete file.
     *
     * @param int $id
     * @return FileModel|null
     */
    public function deleteFile(int $id): ?FileModel
    {
        $user = Auth::user();
        $file = FileModel::where('id', $id)->where('created_by', $user->id)->first();
        if (!$file || !$file->delete()) {
            return null;
        }
        if ($this->fileExists($file)) {
            Storage::delete($this->getFilePath($file));
        }
        return $file;
    }

    /**
     * Create file share.
     *
     * @param int $id
     * @return FileModel|null
     */
    public function createFileShare(int $id): ?FileModel
    {
        $user = Auth::user();
        $file = FileModel::where('id', $id)->where('created_by', $user->id)->first();
        if (!$file) {
            return null;
        }
        if ($file->share_id) {
            return $file;
        }
        $file->share_id = Str::uuid()->toString();
        if (!$file->save()) {
            return null;
        }
        return $file;
    }

    /**
     * Download file service
     *
     * @return FileModel|null
     */
    public function downloadFile(int $id): ?StreamedResponse
    {
        $user = Auth::user();
        $file = FileModel::where('id', $id)->where('created_by', $user->id)->first();
        if (!$file) {
            return null;
        }
        if (!$result = $this->getFileStreamResponse($file)) {
            $file->delete();
            return null;
        }
        return $result;
    }

    /**
     * Download file service
     *
     * @return FileModel|null
     */
    public function downloadSharedFile(string $shareId): ?StreamedResponse
    {
        $file = FileModel::where('share_id', $shareId)->first();
        if (!$file) {
            return null;
        }
        if (!$result = $this->getFileStreamResponse($file)) {
            $file->delete();
            return null;
        }
        return $result;
    }

    private function getFileStreamResponse(FileModel $file): ?StreamedResponse
    {
        if (!$this->fileExists($file)) {
            return null;
        }
        return Storage::download($this->getFilePath($file), $file->name);
    }

    /**
     * Check file exists
     *
     * @param FileModel $file
     * @return bool
     */
    private function fileExists(FileModel $file): bool
    {
        return Storage::exists($this->getFilePath($file));
    }

    /**
     * Get file path
     *
     * @param FileModel $file
     * @return string
     */
    private function getFilePath(FileModel $file): string
    {
        return 'files' . DIRECTORY_SEPARATOR . $file->created_by . DIRECTORY_SEPARATOR . $file->file_name;
    }

    /**
     * Get total user space
     *
     * @param float $size
     * @param string $format
     * @return float
     */
    public static function formatSize(float $size, string $format = 'MB')
    {
        return round(match ($format) {
            'KB' => $size / 1024,
            'MB' => $size / pow(1024, 2),
            'GB' => $size / pow(1024, 3),
            default => $size
        }, 2);
    }
}
