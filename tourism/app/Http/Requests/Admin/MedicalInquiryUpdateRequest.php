<?php

namespace App\Http\Requests\Admin;

class MedicalInquiryUpdateRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:new,reviewing,closed'],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
