<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetField extends Model
{
    protected $fillable = [
        'name',
        'type',
        'rules',
    ];
    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    protected function casts(): array
    {
        return [
            'rules' => 'array',
        ];
    }
}
