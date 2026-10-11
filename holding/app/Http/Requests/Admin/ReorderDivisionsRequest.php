<?php

namespace App\Http\Requests\Admin;

class ReorderDivisionsRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'divisions' => ['required', 'array'],
            'divisions.*.position' => ['required', 'integer', 'min:1', 'max:99'],
            'divisions.*.is_published' => ['nullable', 'boolean'],
        ];
    }
}
