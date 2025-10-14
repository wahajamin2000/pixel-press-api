<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
        // Add authorization logic here (e.g., check if user is superadmin)
//        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId)
            ],
            'price' => 'sometimes|required|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string', // {"length": 30,"width": 20,"height": 1}
            'size_options' => 'nullable|string', // {0: "S",1: "M",2: "L"}
            'color_options' => 'nullable|string', // {0: "White",1: "Black",2: "Gray"}
//            'dimensions.length' => 'nullable|numeric|min:0',
//            'dimensions.width' => 'nullable|numeric|min:0',
//            'dimensions.height' => 'nullable|numeric|min:0',
//            'color_options' => 'nullable|array',
//            'color_options.*' => 'string|max:50',
//            'size_options' => 'nullable|array',
//            'size_options.*' => 'string|max:50',
            'material' => 'nullable|string|max:255',
            'status' => ['sometimes', 'required', new Enum(ProductStatus::class)],
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:product_images,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'This SKU already exists.',
            'price.required' => 'Price is required.',
            'price.min' => 'Price must be greater than or equal to 0.',
            'images.max' => 'You can upload maximum 10 images.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be jpeg, png, jpg, gif, or webp format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
            'delete_images.*.exists' => 'Selected image does not exist.',
            'dimensions.string' => 'Dimensions must be a valid JSON string.',
            'color_options.string' => 'Color options must be a valid JSON array string.',
            'size_options.string' => 'Size options must be a valid JSON array string.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Decode JSON strings to arrays/objects
        if ($this->has('dimensions') && is_string($this->dimensions)) {
            $dimensions = json_decode($this->dimensions, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['dimensions' => $dimensions]);
            }
        }

        if ($this->has('color_options') && is_string($this->color_options)) {
            $colorOptions = json_decode($this->color_options, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['color_options' => $colorOptions]);
            }
        }

        if ($this->has('size_options') && is_string($this->size_options)) {
            $sizeOptions = json_decode($this->size_options, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['size_options' => $sizeOptions]);
            }
        }
    }


    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate JSON format for string inputs
            if ($this->has('dimensions') && is_string($this->dimensions)) {
                $decoded = json_decode($this->dimensions, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    $validator->errors()->add('dimensions', 'Dimensions must be a valid JSON object.');
                    return;
                }
            }

            if ($this->has('color_options') && is_string($this->color_options)) {
                $decoded = json_decode($this->color_options, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    $validator->errors()->add('color_options', 'Color options must be a valid JSON array.');
                    return;
                }
            }

            if ($this->has('size_options') && is_string($this->size_options)) {
                $decoded = json_decode($this->size_options, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    $validator->errors()->add('size_options', 'Size options must be a valid JSON array.');
                    return;
                }
            }

            // Validate dimensions structure after JSON decode
            if ($this->has('dimensions') && is_array($this->dimensions)) {
                $dimensions = $this->dimensions;

                if (isset($dimensions['length']) && (!is_numeric($dimensions['length']) || $dimensions['length'] < 0)) {
                    $validator->errors()->add('dimensions.length', 'Length must be a numeric value greater than or equal to 0.');
                }

                if (isset($dimensions['width']) && (!is_numeric($dimensions['width']) || $dimensions['width'] < 0)) {
                    $validator->errors()->add('dimensions.width', 'Width must be a numeric value greater than or equal to 0.');
                }

                if (isset($dimensions['height']) && (!is_numeric($dimensions['height']) || $dimensions['height'] < 0)) {
                    $validator->errors()->add('dimensions.height', 'Height must be a numeric value greater than or equal to 0.');
                }
            }

            // Validate color options
            if ($this->has('color_options') && is_array($this->color_options)) {
                foreach ($this->color_options as $index => $color) {
                    if (!is_string($color) || strlen($color) > 50) {
                        $validator->errors()->add("color_options.{$index}", 'Each color option must be a string with maximum 50 characters.');
                    }
                }
            }

            // Validate size options
            if ($this->has('size_options') && is_array($this->size_options)) {
                foreach ($this->size_options as $index => $size) {
                    if (!is_string($size) || strlen($size) > 50) {
                        $validator->errors()->add("size_options.{$index}", 'Each size option must be a string with maximum 50 characters.');
                    }
                }
            }
        });
    }
}
