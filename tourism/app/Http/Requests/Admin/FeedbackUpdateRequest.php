<?php

namespace App\Http\Requests\Admin;

class FeedbackUpdateRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:pending,approved,rejected,hidden'],
            'is_verified' => ['nullable', 'boolean'],
            'complaint_status' => ['nullable', 'in:new,reviewing,awaiting,resolved,closed'],
            'note' => ['nullable', 'string', 'max:5000'],
            'response' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
