<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

/**
 * Chuẩn hóa chuỗi tên/tiêu đề (bỏ khoảng trắng thừa, không phân biệt hoa thường)
 * và kiểm tra trùng lặp trước khi thêm mới bản ghi.
 */
class DuplicateNameChecker
{
    /**
     * Chuẩn hóa chuỗi để so sánh không phân biệt chữ hoa/thường và khoảng trắng liên tiếp.
     */
    public static function normalize(?string $value): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim((string) $value)) ?? '';

        return mb_strtolower($collapsed, 'UTF-8');
    }

    /**
     * Kiểm tra giá trị tên/tiêu đề có bị trùng trong phạm vi truy vấn hay không.
     */
    public static function exists(Builder|Relation $query, string $column, ?string $value): bool
    {
        $normalized = self::normalize($value);
        if ($normalized === '') {
            return false;
        }

        $baseQuery = $query instanceof Relation ? $query->getQuery() : $query;

        foreach ((clone $baseQuery)->select($column)->cursor() as $row) {
            if (self::normalize((string) ($row->{$column} ?? '')) === $normalized) {
                return true;
            }
        }

        return false;
    }

    /**
     * Trả về phản hồi chuyển hướng kèm thông báo lỗi trên giao diện Web.
     */
    public static function failWeb(string $field, string $message): RedirectResponse
    {
        return back()
            ->withInput()
            ->with('error', $message)
            ->withErrors([$field => $message]);
    }

    /**
     * Ném ValidationException (HTTP 422 JSON) cho API endpoints.
     */
    public static function failApi(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => [$message]]);
    }
}
