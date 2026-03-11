<?php

namespace App\Modules\Vendor\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuickCreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For quick product creation, we get vendor from authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            $vendor = $user->vendors()->first();
            return $vendor !== null;
        }
        
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:200',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'name.min' => 'Product name must be at least 2 characters.',
            'name.max' => 'Product name may not be greater than 200 characters.',
            'description.max' => 'Description may not be greater than 2000 characters.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            'stock_quantity.required' => 'Quantity is required.',
            'stock_quantity.integer' => 'Quantity must be an integer.',
            'stock_quantity.min' => 'Quantity must be at least 0.',
        ];
    }
}
