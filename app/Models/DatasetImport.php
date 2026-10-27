<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetImport extends Model
{
    protected $fillable = [
        'original_filename',
        'storage_path',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
    ];
    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }
}
