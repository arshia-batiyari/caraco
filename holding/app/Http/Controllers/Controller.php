<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    use AuthorizesRequests;

    protected function requireDeleteConfirmation(): void
    {
        if (request()->input('confirm_delete') !== '1') {
            throw ValidationException::withMessages([
                'confirm_delete' => __('admin.validation.confirm_delete'),
            ]);
        }
    }
}
