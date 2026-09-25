<?php

namespace Tests\Unit\Rules;

use App\Rules\MaxWords;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MaxWordsTest extends TestCase
{
    #[DataProvider('validContent')]
    public function test_content_at_or_below_the_limit_passes(string $content): void
    {
        $messages = [];

        (new MaxWords(3))->validate('summary', $content, function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->assertSame([], $messages);
    }

    public static function validContent(): array
    {
        return [
            'empty content' => [''],
            'exactly three words' => ['one two three'],
            'rich text tags are ignored' => ['<p>one <strong>two</strong> three</p>'],
            'adjacent rich text blocks stay separate' => ['<p>one</p><p>two</p><p>three</p>'],
            'unicode and apostrophes count naturally' => ['Nature’s care matters'],
        ];
    }

    public function test_content_above_the_limit_fails(): void
    {
        $messages = [];

        (new MaxWords(3))->validate('summary', 'one two three four', function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->assertSame(['The :attribute field must not contain more than 3 words.'], $messages);
    }
}
