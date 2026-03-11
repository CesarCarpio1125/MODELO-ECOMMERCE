<?php

namespace App\Modules\Vendor\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For regular product creation, we get vendor from authenticated user
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
            'stock' => [
                'sometimes',
                'integer',
                'min:0',
                'max:999999',
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                'unique:products,sku',
            ],
            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'dimensions' => [
                'nullable',
                'array',
            ],
            'dimensions.length' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'dimensions.width' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'dimensions.height' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'dimensions.unit' => [
                'nullable',
                'string',
                'in:cm,in',
            ],
            'status' => [
                'nullable',
                'string',
                'in:draft,active,archived',
            ],
            'category_id' => [
                'nullable',
                'string',
            ],
            'tags' => [
                'nullable',
                'array',
                'max:10',
            ],
            'tags.*' => [
                'string',
                'max:50',
            ],
            'featured_image' => [
                'nullable',
                'image',
                'max:2048',
                'mimes:jpeg,png,jpg,gif,svg,webp',
            ],
            'images' => [
                'nullable',
                'array',
                'max:10',
            ],
            'images.*' => [
                'image',
                'max:2048',
                'mimes:jpeg,png,jpg,gif,svg,webp',
            ],
            'variants' => [
                'nullable',
                'array',
                'max:20',
            ],
            'variants.*.name' => [
                'required',
                'string',
                'max:100',
            ],
            'variants.*.price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'variants.*.stock' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],
            'variants.*.weight' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'variants.*.attributes' => [
                'nullable',
                'array',
            ],
            'variants.*.image' => [
                'nullable',
                'image',
                'max:2048',
                'mimes:jpeg,png,jpg,gif,svg,webp',
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
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'stock_quantity.integer' => 'Stock quantity must be an integer.',
            'stock_quantity.min' => 'Stock quantity must be at least 0.',
            'sku.unique' => 'SKU has already been taken.',
            'featured_image.image' => 'The featured image must be an image file.',
            'featured_image.max' => 'The featured image may not be greater than 2MB.',
            'images.*.image' => 'Each image must be an image file.',
            'images.*.max' => 'Each image may not be greater than 2MB.',
            'variants.*.name.required' => 'Variant name is required.',
            'variants.*.price.required' => 'Variant price is required.',
            'variants.*.stock.required' => 'Variant stock is required.',
        ];
    }
}
