<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Internationalization
 *
 * Handles multi-language support
 */
class I18n
{
    private static ?array $translations = null;
    private static string $locale = 'en';

    /**
     * Set current locale
     */
    public static function setLocale(string $locale): void
    {
        self::$locale = $locale;
        self::$translations = null; // Reset cache
    }

    /**
     * Get current locale
     */
    public static function getLocale(): string
    {
        return self::$locale;
    }

    /**
     * Load translations for current locale
     */
    private static function loadTranslations(): void
    {
        if (self::$translations !== null) {
            return;
        }

        $file = APP_PATH . '/Lang/' . self::$locale . '.php';

        if (!file_exists($file)) {
            $file = APP_PATH . '/Lang/en.php'; // Fallback to English
        }

        self::$translations = require $file;
    }

    /**
     * Translate key
     */
    public static function translate(string $key, array $replacements = []): string
    {
        self::loadTranslations();

        $translation = self::$translations[$key] ?? $key;

        // Replace placeholders
        foreach ($replacements as $placeholder => $value) {
            $translation = str_replace("{{$placeholder}}", $value, $translation);
        }

        return $translation;
    }
}

/**
 * Translation helper function
 */
function __(string $key, array $replacements = []): string
{
    return I18n::translate($key, $replacements);
}
