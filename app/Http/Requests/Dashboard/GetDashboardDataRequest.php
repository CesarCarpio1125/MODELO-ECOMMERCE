<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class GetDashboardDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'days' => 'sometimes|integer|min:1|max:365',
            'limit' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function getDays(): int
    {
        return $this->get('days', 30);
    }

    public function getLimit(): int
    {
        return $this->get('limit', 10);
    }
}
