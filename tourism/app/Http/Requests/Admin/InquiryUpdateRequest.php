<?php

namespace App\Http\Requests\Admin;

class InquiryUpdateRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('assigned_to') === '') {
            $this->merge(['assigned_to' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:new,reviewing,assigned,closed'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
