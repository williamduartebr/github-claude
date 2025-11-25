<?php

namespace Src\VehicleDataCenter\Helpers;

use Illuminate\Support\Str;

class VehicleHelpers
{
    /**
     * Normalize vehicle name for slug generation
     */
    public static function normalizeVehicleName(string $name): string
    {
        // Remove special characters
        $name = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $name);

        // Convert to lowercase
        $name = strtolower($name);

        // Replace spaces with hyphens
        $name = str_replace(' ', '-', $name);

        // Remove multiple hyphens
        $name = preg_replace('/-+/', '-', $name);

        return trim($name, '-');
    }

    /**
     * Build full vehicle name
     */
    public static function buildFullName(string $make, string $model, string $version, int $year): string
    {
        return "{$make} {$model} {$version} {$year}";
    }

    /**
     * Format power output
     */
    public static function formatPower(?float $hp, ?float $kw = null): string
    {
        if (!$hp) {
            return 'N/A';
        }

        $formatted = "{$hp} cv";

        if ($kw) {
            $formatted .= " ({$kw} kW)";
        }

        return $formatted;
    }

    /**
     * Format fuel consumption
     */
    public static function formatConsumption(?float $kmPerLiter): string
    {
        if (!$kmPerLiter) {
            return 'N/A';
        }

        return "{$kmPerLiter} km/l";
    }

    /**
     * Format dimensions
     */
    public static function formatDimensions(?int $mm): string
    {
        if (!$mm) {
            return 'N/A';
        }

        $meters = $mm / 1000;
        return number_format($meters, 2, ',', '.') . ' m';
    }

    /**
     * Translate fuel type
     */
    public static function translateFuelType(?string $fuelType): string
    {
        $translations = [
            'gasoline' => 'Gasolina',
            'diesel' => 'Diesel',
            'ethanol' => 'Etanol',
            'flex' => 'Flex (Gasolina/Etanol)',
            'electric' => 'Elétrico',
            'hybrid' => 'Híbrido',
            'plugin_hybrid' => 'Híbrido Plug-in',
            'cng' => 'GNV'
        ];

        return $translations[$fuelType] ?? 'N/A';
    }

    /**
     * Translate transmission type
     */
    public static function translateTransmission(?string $transmission): string
    {
        $translations = [
            'manual' => 'Manual',
            'automatic' => 'Automático',
            'cvt' => 'CVT',
            'dct' => 'DCT',
            'amt' => 'AMT'
        ];

        return $translations[$transmission] ?? 'N/A';
    }

    /**
     * Translate category
     */
    public static function translateCategory(?string $category): string
    {
        $translations = [
            'sedan' => 'Sedã',
            'hatch' => 'Hatchback',
            'suv' => 'SUV',
            'pickup' => 'Picape',
            'van' => 'Van',
            'coupe' => 'Cupê',
            'convertible' => 'Conversível',
            'wagon' => 'Station Wagon',
            'sport' => 'Esportivo',
            'motorcycle' => 'Motocicleta',
            'truck' => 'Caminhão',
            'bus' => 'Ônibus',
            'other' => 'Outro'
        ];

        return $translations[$category] ?? 'N/A';
    }

    /**
     * Resolve version slug conflicts
     */
    public static function resolveSlugConflict(string $baseSlug, int $modelId, int $year): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (self::slugExists($slug, $modelId, $year)) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private static function slugExists(string $slug, int $modelId, int $year): bool
    {
        return \VehicleDataCenter\Domain\Eloquent\VehicleVersion::where('model_id', $modelId)
            ->where('year', $year)
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * Extract year from mixed string
     */
    public static function extractYear(string $text): ?int
    {
        if (preg_match('/\b(19|20)\d{2}\b/', $text, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    /**
     * Format price
     */
    public static function formatPrice(?float $price): string
    {
        if (!$price) {
            return 'Consulte';
        }

        return 'R$ ' . number_format($price, 2, ',', '.');
    }

    /**
     * Generate SEO-friendly URL
     */
    public static function generateSeoUrl(string $make, string $model, int $year, string $version): string
    {
        $makeSlug = Str::slug($make);
        $modelSlug = Str::slug($model);
        $versionSlug = Str::slug($version);

        return "/veiculos/{$makeSlug}/{$modelSlug}/{$year}/{$versionSlug}";
    }

    /**
     * Parse engine code
     */
    public static function parseEngineCode(?string $engineCode): array
    {
        if (!$engineCode) {
            return [
                'displacement' => null,
                'type' => null,
                'code' => null
            ];
        }

        // Try to extract displacement (e.g., "1.0", "2.0T", "1.5 TSI")
        preg_match('/(\d+\.?\d*)[LT]?/', $engineCode, $displacement);

        // Try to extract type (e.g., "TSI", "TDI", "TFSI")
        preg_match('/[A-Z]{2,4}/', $engineCode, $type);

        return [
            'displacement' => $displacement[1] ?? null,
            'type' => $type[0] ?? null,
            'code' => $engineCode
        ];
    }
}
