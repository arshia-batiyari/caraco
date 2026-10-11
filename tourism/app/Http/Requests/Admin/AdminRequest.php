<?php

namespace App\Http\Requests\Admin;

use App\Support\HttpUrl;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'editor'], true);
    }

    /**
     * @return array<int, mixed>
     */
    protected function dropEmptyUploads(string $key): void
    {
        $current = $this->files->get($key);
        if ($current === null) {
            return;
        }

        $list = is_array($current) ? $current : [$current];
        $valid = [];
        foreach ($list as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $valid[] = $file;
            }
        }

        if ($valid === []) {
            $this->files->remove($key);
        } else {
            $this->files->set($key, is_array($current) ? $valid : $valid[0]);
        }

        $this->convertedFiles = null;
    }

    /**
     * @return array<int, mixed>
     */
    protected function canonicalRule(): array
    {
        return ['nullable', 'string', 'max:255', function (string $attribute, mixed $value, Closure $fail) {
            if (filled($value) && ! HttpUrl::valid((string) $value)) {
                $fail(__('admin.validation.canonical'));
            }
        }];
    }

    /**
     * @return array<int, mixed>
     */
    protected function requirePublishedText(Validator $validator, string $column): void
    {
        $validator->after(function (Validator $validator) use ($column) {
            if (! $this->boolean('is_published') || $validator->errors()->isNotEmpty()) {
                return;
            }
            foreach ((array) $this->input('translations', []) as $row) {
                if (is_array($row) && filled($row[$column] ?? null)) {
                    return;
                }
            }
            $validator->errors()->add('is_published', __('admin.validation.publish'));
        });
    }

    protected function requireCoordinatePair(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $lat = $this->input('latitude');
            $lng = $this->input('longitude');
            $hasLat = filled($lat);
            $hasLng = filled($lng);
            if ($hasLat !== $hasLng) {
                $validator->errors()->add('latitude', __('admin.validation.coordinates'));
            }
        });
    }

    protected function httpRule(bool $required = false): array
    {
        return array_values(array_filter([
            $required ? 'required' : 'nullable',
            'string',
            'max:255',
            function (string $attribute, mixed $value, Closure $fail) use ($required) {
                if (! filled($value)) {
                    if ($required) {
                        $fail(__('admin.validation.url'));
                    }

                    return;
                }
                if (! HttpUrl::valid((string) $value)) {
                    $fail(__('admin.validation.url'));
                }
            },
        ]));
    }
}
