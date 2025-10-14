<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateProductCategoryRequest extends FormRequest
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
        $productId = $this->route('product_category')->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'slug' => ['sometimes', 'required', Rule::unique('product_categories', 'slug')->ignore($productId)],
            'status' => ['sometimes', 'required', new Enum(StatusEnum::class)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product category name is required.',
            'slug.unique' => 'Product category already available.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name') && !$this->has('slug')) {
            $this->merge(['slug' => generate_slug($this->name, 32, 'ctg-')]);
        }

    }
}
