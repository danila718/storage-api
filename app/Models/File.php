<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class File extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'dir_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'file_name',
//        'file_size',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function (File $model) {
            $user = Auth::user();
            $model->created_by = $user->id;
        });
    }

    /**
     * Total size of user files.
     *
     * @param int $userId
     * @param int|null $dirId
     * @return int
     */
    public static function totalUserFilesSize(int $userId, ?int $dirId = null): int
    {
        $q = self::where('created_by', $userId);
        if ($dirId) {
            $q->where('dir_id', $dirId);
        }
        return (int) $q->sum('file_size');
    }
}
