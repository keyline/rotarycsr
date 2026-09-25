<?php

namespace App\Services;

use App\Models\Setting;

final class DashboardContent
{
    public const SETTING_KEY = 'dashboard_how_it_works';

    public const DEFAULT_HOW_IT_WORKS = <<<'TEXT'
Complete the application in short, guided steps - your answers are saved automatically as you type.
You can leave at any point and pick up right where you left off, right up until the deadline.
Review everything on the final step before submitting.
Once submitted, the application is locked and cannot be edited further.
TEXT;

    public static function howItWorks(): string
    {
        return trim((string) Setting::get(self::SETTING_KEY, self::DEFAULT_HOW_IT_WORKS));
    }

    /**
     * @return list<string>
     */
    public static function howItWorksSteps(): array
    {
        $lines = preg_split('/\R/u', self::howItWorks()) ?: [];
        $steps = array_map(static fn (string $line): string => trim($line), $lines);

        return array_values(array_filter($steps, static fn (string $step): bool => $step !== ''));
    }
}
