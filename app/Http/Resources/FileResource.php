<?php

namespace App\Http\Resources;

use App\Services\StorageService;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'size' => StorageService::getDisplaySize($this->file_size),
            'dir_id' => $this->dir_id,
            'share_link' => $this->share_id ? StorageService::getSharedLink($this->share_id) : null,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
