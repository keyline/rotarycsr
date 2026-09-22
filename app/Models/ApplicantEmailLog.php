<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantEmailLog extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicantEmailLogFactory> */
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'application_id',
        'sent_by',
        'type',
        'recipient_email',
        'recipient_name',
        'subject',
        'body',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function bodyAsText(): string
    {
        $bodyWithLineBreaks = preg_replace(
            '/<\s*br\s*\/?\s*>|<\/\s*(?:p|div|li)\s*>/i',
            "\n",
            $this->body,
        ) ?? $this->body;

        return trim(html_entity_decode(strip_tags($bodyWithLineBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
