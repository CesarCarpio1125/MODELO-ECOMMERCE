<?php

namespace App\Http\Requests\Help;

use Illuminate\Foundation\Http\FormRequest;

class GetHelpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section' => 'sometimes|string|max:50',
            'search' => 'sometimes|string|max:100',
        ];
    }
}
