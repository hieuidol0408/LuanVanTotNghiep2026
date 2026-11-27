<?php

namespace App\Support;

use App\Models\HoSoTrinh;
use Carbon\Carbon;

class SearchCodeHelper
{
    /**
     * Trích xuất ID số từ chuỗi tìm kiếm dạng mã (VD: "#1", "1", "BQ-001", "#HS-12", "mã 5").
     *
     * @param  array<int, string>  $prefixes
     */
    public static function extractNumericId(?string $search, array $prefixes = []): ?int
    {
        $normalized = trim((string) $search);
        if ($normalized === '') {
            return null;
        }

        $prefixPatterns = ['ma\s*#?\s*', 'mã\s*#?\s*'];
        $codeOnlyPatterns = [];

        foreach ($prefixes as $prefix) {
            $escaped = preg_quote(trim($prefix), '/');
            if ($escaped !== '') {
                $prefixPatterns[] = $escaped.'(?:\s*-\s*|\s+)?';
                $codeOnlyPatterns[] = '#?'.$escaped.'(?:\s*-\s*|\s*)0*([1-9][0-9]*)';
            }
        }

        $prefixGroup = implode('|', $prefixPatterns);
        if (preg_match('/^#?(?:'.$prefixGroup.')?0*([1-9][0-9]*)$/iu', $normalized, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/(?:^|\s)#0*([1-9][0-9]*)\b/u', $normalized, $matches)) {
            return (int) $matches[1];
        }

        foreach ($codeOnlyPatterns as $pattern) {
            if (preg_match('/(?:^|\s)'.$pattern.'\b/iu', $normalized, $matches)) {
                return (int) $matches[1];
            }
        }

        return null;
    }

    /**
     * Tìm danh sách MaHoSo khớp theo mã hồ sơ gốc (#1, 1) hoặc mã hiển thị (#HS-MMYYNN).
     *
     * @return array<int, int>
     */
    public static function findMatchingDossierIds(?string $search): array
    {
        $normalized = trim((string) $search);
        if ($normalized === '') {
            return [];
        }

        $numericId = self::extractNumericId($normalized, ['hs', 'hst']);
        $compactQuery = mb_strtoupper(preg_replace('/[\s#]/u', '', $normalized) ?? '', 'UTF-8');

        $looksLikeCode = $numericId !== null
            || str_contains($compactQuery, 'HS')
            || preg_match('/^[0-9]{2,6}$/', $compactQuery) === 1;

        if (! $looksLikeCode) {
            return [];
        }

        $matchedIds = [];
        $counters = [];

        $rows = HoSoTrinh::withoutGlobalScopes()
            ->select(['MaHoSo', 'NgayTrinh'])
            ->orderBy('MaHoSo', 'asc')
            ->get();

        foreach ($rows as $row) {
            $date = $row->NgayTrinh ? Carbon::parse($row->NgayTrinh) : Carbon::now();
            $mmyy = $date->format('my');
            $counters[$mmyy] = ($counters[$mmyy] ?? 0) + 1;

            $displayCode = sprintf('HS-%s%02d', $mmyy, $counters[$mmyy]);

            if (
                ($numericId !== null && (int) $row->MaHoSo === $numericId)
                || str_contains($displayCode, $compactQuery)
            ) {
                $matchedIds[] = (int) $row->MaHoSo;
            }
        }

        return array_values(array_unique($matchedIds));
    }
}
