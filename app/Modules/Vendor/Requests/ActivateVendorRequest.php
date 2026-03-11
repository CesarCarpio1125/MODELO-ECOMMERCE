<?php

namespace App\Modules\Vendor\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivateVendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'unique:vendors,store_name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'store_image' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Accept both file uploads and base64 images (for NativePHP)
                    if (is_null($value) || $value === '') {
                        return; // nullable, so it's ok
                    }
                    
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        // Regular file upload - validate as image
                        $validator = \Illuminate\Support\Facades\Validator::make(
                            ['image' => $value],
                            ['image' => 'image|max:2048|mimes:jpeg,png,jpg,gif,svg,webp']
                        );
                        
                        if ($validator->fails()) {
                            $fail($validator->errors()->first('image'));
                        }
                    } elseif (is_string($value)) {
                        // Base64 encoded image - validate format
                        if (!preg_match('/^data:image\/(\w+);base64,/', $value)) {
                            $fail('The file must be a valid image.');
                        }
                    } else {
                        $fail('The file must be an image.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'store_name.required' => 'Store name is required.',
            'store_name.min' => 'Store name must be at least 2 characters.',
            'store_name.max' => 'Store name may not be greater than 100 characters.',
            'store_name.unique' => 'Store name has already been taken.',
            'description.max' => 'Description may not be greater than 1000 characters.',
        ];
    }
}
