<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;
use Symfony\Component\Mime\Exception\InvalidArgumentException as MimeInvalidArgumentException;

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
            $fail('The :attribute field must be a supported JPG, JPEG, PNG, WEBP, MP4, MOV, or WEBM file.');

            return;
        }

        if (! $value->isValid()) {
            $fail('The :attribute failed to upload. Please try again.');

            return;
        }

        $path = $value->getRealPath();

        if ($path === false || ! is_readable($path)) {
            $fail('The :attribute failed to upload. Please try again.');

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());

        try {
            $mimeType = strtolower($value->getMimeType() ?: 'application/octet-stream');
        } catch (MimeInvalidArgumentException) {
            $fail('The :attribute failed to upload. Please try again.');

            return;
        }

        if (! in_array($mimeType, self::MIME_TYPES_BY_EXTENSION[$extension] ?? [], true)
            && ! ($mimeType === 'application/octet-stream' && $this->hasRecognizedVideoSignature($path, $extension))) {
            $fail('The :attribute field must be a supported JPG, JPEG, PNG, WEBP, MP4, MOV, or WEBM file.');

            return;
        }

        $isImage = str_starts_with($mimeType, 'image/');
        $maximumBytes = $isImage ? 100 * 1024 : 2 * 1024 * 1024;

        if ($value->getSize() > $maximumBytes) {
            $fail($isImage
                ? 'The :attribute field must not be greater than 100 KB.'
                : 'The :attribute field must not be greater than 2 MB.');
        }
    }

    private function hasRecognizedVideoSignature(string $path, string $extension): bool
    {
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
