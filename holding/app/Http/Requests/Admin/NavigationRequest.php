<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use App\Support\HttpUrl;
use Illuminate\Validation\Rule;

class NavigationRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $routes = ['home', 'about', 'divisions.index', 'projects.index', 'contact'];

        return [
            'items' => ['nullable', 'array'],
            'items.*.location' => ['required', 'in:header,footer'],
            'items.*.kind' => ['required', 'in:route,page,url'],
            'items.*.route_name' => ['nullable', Rule::in($routes)],
            'items.*.page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'items.*.url' => ['nullable', 'string', 'max:255'],
            'items.*.position' => ['required', 'integer', 'min:0', 'max:999'],
            'items.*.is_published' => ['nullable', 'boolean'],
            'items.*.labels' => ['nullable', 'array'],
            'items.*.labels.*' => ['nullable', 'string', 'max:80'],
            'remove' => ['nullable', 'array'],
            'remove.*' => ['integer'],
            'new' => ['nullable', 'array'],
            'new.location' => ['nullable', 'in:header,footer'],
            'new.kind' => ['nullable', 'in:route,page,url'],
            'new.route_name' => ['nullable', Rule::in($routes)],
            'new.page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'new.url' => ['nullable', 'string', 'max:255'],
            'new.position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'new.is_published' => ['nullable', 'boolean'],
            'new.labels' => ['nullable', 'array'],
            'new.labels.*' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach (['items', 'new'] as $group) {
                $rows = $group === 'new' ? [$this->input('new', [])] : $this->input('items', []);
                if (! is_array($rows)) {
                    continue;
                }
                foreach ($rows as $index => $row) {
                    if (! is_array($row) || ! filled($row['kind'] ?? null)) {
                        continue;
                    }
                    $prefix = $group === 'new' ? 'new' : 'items.'.$index;
                    $this->checkLink($validator, $prefix, $row);
                }
            }
        });
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function checkLink($validator, string $prefix, array $row): void
    {
        $kind = (string) ($row['kind'] ?? '');
        if ($kind === 'route' && ! filled($row['route_name'] ?? null)) {
            $validator->errors()->add($prefix.'.route_name', __('admin.validation.required'));
        }
        if ($kind === 'page' && ! Page::query()->whereKey($row['page_id'] ?? null)->exists()) {
            $validator->errors()->add($prefix.'.page_id', __('admin.validation.required'));
        }
        if ($kind === 'url' && ! HttpUrl::valid(isset($row['url']) ? (string) $row['url'] : null)) {
            $validator->errors()->add($prefix.'.url', __('admin.validation.url'));
        }
        if (in_array($kind, ['page', 'url'], true)) {
            $labels = is_array($row['labels'] ?? null) ? $row['labels'] : [];
            $hasLabel = false;
            foreach ($labels as $label) {
                if (filled(trim((string) $label))) {
                    $hasLabel = true;
                }
            }
            if (! $hasLabel) {
                $validator->errors()->add($prefix.'.labels.'.config('app.locale'), __('admin.validation.title'));
            }
        }
    }
}
