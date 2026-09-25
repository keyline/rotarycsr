<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class MaxWords implements ValidationRule
{
    public function __construct(private readonly int $maximum)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $withoutTags = preg_replace('/<[^>]*>/', ' ', $value) ?? $value;
        $plainText = html_entity_decode(strip_tags($withoutTags), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        preg_match_all('/[\p{L}\p{N}]+(?:[\'\x{2019}-][\p{L}\p{N}]+)*/u', $plainText, $words);

        if (count($words[0]) > $this->maximum) {
            $fail("The :attribute field must not contain more than {$this->maximum} words.");
        }
    }
}
