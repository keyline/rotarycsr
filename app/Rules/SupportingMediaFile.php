<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

class SupportingMediaFile implements ValidationRule
{
    /** @var array<string, array<int, string>> */
    private const MIME_TYPES_BY_EXTENSION = [
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'mp4' => ['video/mp4', 'application/mp4', 'video/x-m4v', 'video/quicktime'],
        'mov' => ['video/quicktime'],
        'webm' => ['video/webm'],
    ];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $mimeType = strtolower($value->getMimeType() ?: 'application/octet-stream');

        if (in_array($mimeType, self::MIME_TYPES_BY_EXTENSION[$extension] ?? [], true)) {
            return;
        }

        if ($mimeType === 'application/octet-stream' && $this->hasRecognizedVideoSignature($value, $extension)) {
            return;
        }

        $fail('The :attribute field must be a supported JPG, JPEG, PNG, WEBP, MP4, MOV, or WEBM file.');
    }

    private function hasRecognizedVideoSignature(UploadedFile $file, string $extension): bool
    {
        $path = $file->getRealPath();

        if ($path === false) {
            return false;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 4096);
        fclose($handle);

        if (! is_string($header)) {
            return false;
        }

        return match ($extension) {
            'mp4', 'mov' => substr($header, 4, 4) === 'ftyp',
            'webm' => str_starts_with($header, "\x1A\x45\xDF\xA3"),
            default => false,
        };
    }
}
