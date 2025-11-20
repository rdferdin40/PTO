<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Helper Functions
 *
 * Utility functions used throughout the application
 */
class Helpers
{
    /**
     * Escape HTML for XSS protection
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get old input value (for form repopulation after validation errors)
     */
    public static function old(string $key, mixed $default = ''): mixed
    {
        return Session::get('_old_input')[$key] ?? $default;
    }

    /**
     * Calculate leave duration in days
     */
    public static function calculateLeaveDays(
        string $startDate,
        string $endDate,
        string $startHalf,
        string $endHalf,
        array $workingDays,
        array $holidays
    ): float {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $days = 0.0;

        $holidayDates = array_map(fn($h) => $h['date'], $holidays);

        while ($start <= $end) {
            $dateStr = $start->format('Y-m-d');
            $weekday = (int) $start->format('w'); // 0=Sunday

            // Skip non-working days and holidays
            if (isset($workingDays[$weekday]) && $workingDays[$weekday]['is_working_day'] && !in_array($dateStr, $holidayDates)) {
                // Check for half-day
                if ($start->format('Y-m-d') === $startDate && $startHalf !== 'full') {
                    $days += 0.5;
                } elseif ($start->format('Y-m-d') === $endDate && $endHalf !== 'full') {
                    $days += 0.5;
                } else {
                    $days += 1.0;
                }
            }

            $start->modify('+1 day');
        }

        return $days;
    }

    /**
     * Format date for display
     */
    public static function formatDate(string $date, string $format = 'M d, Y'): string
    {
        return date($format, strtotime($date));
    }

    /**
     * Get user's theme
     */
    public static function getUserTheme(?array $user, ?array $company): string
    {
        if ($user && $user['theme']) {
            return $user['theme'];
        }

        if ($company && $company['default_theme']) {
            return $company['default_theme'];
        }

        return 'light';
    }

    /**
     * Get user's locale
     */
    public static function getUserLocale(?array $user, ?array $company): string
    {
        if ($user && $user['locale']) {
            return $user['locale'];
        }

        if ($company && $company['default_locale']) {
            return $company['default_locale'];
        }

        return 'en';
    }

    /**
     * Check if dates overlap with existing leaves
     */
    public static function checkLeaveOverlap(
        \PDO $db,
        int $userId,
        string $startDate,
        string $endDate,
        ?int $excludeLeaveId = null
    ): bool {
        $sql = "SELECT COUNT(*) as count FROM leaves
                WHERE user_id = ?
                AND status IN ('pending', 'approved')
                AND (
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date >= ? AND end_date <= ?)
                )";

        $params = [$userId, $endDate, $startDate, $startDate, $startDate, $startDate, $endDate];

        if ($excludeLeaveId) {
            $sql .= " AND id != ?";
            $params[] = $excludeLeaveId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Check if date range contains blackout dates
     */
    public static function checkBlackoutDates(
        \PDO $db,
        int $companyId,
        string $startDate,
        string $endDate
    ): array {
        $sql = "SELECT * FROM holidays
                WHERE company_id = ?
                AND is_blackout = 1
                AND date BETWEEN ? AND ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$companyId, $startDate, $endDate]);

        return $stmt->fetchAll();
    }

    /**
     * Generate random token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}

// Global helper function for HTML escaping
if (!function_exists('e')) {
    function e(?string $value): string {
        return Helpers::e($value);
    }
}

// Global helper for old input
if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed {
        return Helpers::old($key, $default);
    }
}
