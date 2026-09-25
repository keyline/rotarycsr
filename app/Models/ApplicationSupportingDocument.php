<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationSupportingDocument extends Model
{
    protected $fillable = [
        'application_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'media_type',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
