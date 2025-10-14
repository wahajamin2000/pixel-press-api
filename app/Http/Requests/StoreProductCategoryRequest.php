<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProductCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:50|unique:product_categories,slug',
            'description' => 'nullable|string',
            'status' => ['nullable', new Enum(StatusEnum::class)],
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

        if (!$this->has('status')) {
            $this->merge(['status' => StatusEnum::Active->value]);
        }
    }

}



