<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PgArrayCast implements CastsAttributes
{
    /**
     * Cast the given value from database representation.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (empty($value)) {
            return [];
        }

        // If it is already a PHP array
        if (is_array($value)) {
            return $value;
        }

        // If it is in PostgreSQL {value1,value2} format
        if (is_string($value) && str_starts_with($value, '{') && str_ends_with($value, '}')) {
            $content = substr($value, 1, -1);
            if (empty($content)) {
                return [];
            }
            
            // Match quoted strings or non-comma values
            preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|([^,]+)/', $content, $matches);
            
            $result = [];
            for ($i = 0; $i < count($matches[0]); $i++) {
                if ($matches[1][$i] !== '') {
                    $result[] = stripcslashes($matches[1][$i]);
                } else {
                    $result[] = trim($matches[2][$i]);
                }
            }
            return $result;
        }

        // Fallback to JSON decoding if stored as JSON string
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [$value];
        }

        return [];
    }

    /**
     * Prepare the given value for storage in the database.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (empty($value)) {
            return '{}';
        }

        if (is_string($value)) {
            // If it is already in PostgreSQL syntax, return it directly
            if (str_starts_with($value, '{') && str_ends_with($value, '}')) {
                return $value;
            }
            $value = [$value];
        }

        if (is_array($value)) {
            $escaped = array_map(function ($el) {
                // Escape backslashes and double quotes
                $el = str_replace('\\', '\\\\', $el);
                $el = str_replace('"', '\\"', $el);
                return '"' . $el . '"';
            }, $value);
            return '{' . implode(',', $escaped) . '}';
        }

        return '{}';
    }
}
