<?php

namespace Tests\Unit\Rules;

use App\Rules\SupportingMediaFile;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SupportingMediaFileTest extends TestCase
{
    #[DataProvider('supportedMedia')]
    public function test_supported_media_passes(UploadedFile $file): void
    {
        $messages = [];

        (new SupportingMediaFile)->validate('document', $file, function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->assertSame([], $messages);
    }

    public static function supportedMedia(): array
    {
        return [
            'standard MP4 MIME type' => [UploadedFile::fake()->create('video.mp4', 1, 'video/mp4')],
            'application MP4 MIME type' => [UploadedFile::fake()->create('video.mp4', 1, 'application/mp4')],
            'generic MIME type with MP4 signature' => [
                UploadedFile::fake()
                    ->createWithContent('video.mp4', pack('N', 24).'ftypisom'.str_repeat("\0", 12))
                    ->mimeType('application/octet-stream'),
            ],
            'JPEG image' => [UploadedFile::fake()->create('photo.jpg', 1, 'image/jpeg')],
        ];
    }

    public function test_generic_file_disguised_as_mp4_is_rejected(): void
    {
        $messages = [];
        $file = UploadedFile::fake()
            ->createWithContent('script.mp4', '<?php echo "not a video";')
            ->mimeType('application/octet-stream');

        (new SupportingMediaFile)->validate('document', $file, function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->assertSame([
            'The :attribute field must be a supported JPG, JPEG, PNG, WEBP, MP4, MOV, or WEBM file.',
        ], $messages);
    }

    public function test_unreadable_upload_fails_validation_without_throwing_an_exception(): void
    {
        $messages = [];
        $file = new UploadedFile('', 'video.mp4', 'video/mp4', UPLOAD_ERR_INI_SIZE, true);

        (new SupportingMediaFile)->validate('document', $file, function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->assertSame([
            'The :attribute failed to upload. Please try again.',
        ], $messages);
    }
}
