<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWS_Facts
{
    private static ?array $facts = null;

    public static function get(string $key): ?string
    {
        $value = self::load()[$key] ?? null;
        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    private static function load(): array
    {
        if (self::$facts === null) {
            $decoded = json_decode((string) file_get_contents(PWS_RELEASE_DIR . 'payload/facts.json'), true);
            self::$facts = is_array($decoded['facts'] ?? null) ? $decoded['facts'] : array();
        }
        return self::$facts;
    }
}
