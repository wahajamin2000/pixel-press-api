<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class OldStoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Guest checkout allowed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Customer information
            'customer' => 'required|array',
            'customer.first_name' => 'required|string|max:255',
            'customer.last_name' => 'required|string|max:255',
            'customer.email' => 'required|email|max:255',
            'customer.phone' => 'nullable|string|max:20',

            // Billing address
            'billing_address' => 'required|array',
            'billing_address.address_line_1' => 'required|string|max:255',
            'billing_address.address_line_2' => 'nullable|string|max:255',
            'billing_address.city' => 'required|string|max:100',
            'billing_address.state' => 'required|string|max:100',
            'billing_address.postal_code' => 'required|string|max:20',
            'billing_address.country' => 'required|string|max:100',

            // Shipping address (optional, defaults to billing)
            'shipping_address' => 'nullable|array',
            'shipping_address.address_line_1' => 'required_with:shipping_address|string|max:255',
            'shipping_address.address_line_2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required_with:shipping_address|string|max:100',
            'shipping_address.state' => 'required_with:shipping_address|string|max:100',
            'shipping_address.postal_code' => 'required_with:shipping_address|string|max:20',
            'shipping_address.country' => 'required_with:shipping_address|string|max:100',

            // Order items
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.design_specifications' => 'nullable|array',
            'items.*.dimensions' => 'nullable|array',
            'items.*.dimensions.width' => 'nullable|numeric|min:0',
            'items.*.dimensions.height' => 'nullable|numeric|min:0',
            'items.*.color_options' => 'nullable|array',
            'items.*.special_instructions' => 'nullable|string|max:1000',
//            'items.*.gang_sheet_position' => 'nullable|array',
            'items.*.design_files' => 'nullable|array|max:10',
            'items.*.design_files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf,ai,eps|max:1024',

            // Order level information
            'notes' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:1000',
//            'is_gang_sheet' => 'boolean',
//            'gang_sheet_data' => 'nullable|array',

            'discount_code' => 'nullable|string|max:50',
            'shipping_method' => 'nullable|string|in:standard,express,overnight',
            'tax_exempt' => 'boolean',
            'custom_discount_amount' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer.required' => 'Customer information is required.',
            'customer.first_name.required' => 'First name is required.',
            'customer.last_name.required' => 'Last name is required.',
            'customer.email.required' => 'Email address is required.',
            'customer.email.email' => 'Please provide a valid email address.',

            'billing_address.required' => 'Billing address is required.',
            'billing_address.address_line_1.required' => 'Address line 1 is required.',
            'billing_address.city.required' => 'City is required.',
            'billing_address.state.required' => 'State is required.',
            'billing_address.postal_code.required' => 'Postal code is required.',
            'billing_address.country.required' => 'Country is required.',

            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.product_id.required' => 'Product ID is required for each item.',
            'items.*.product_id.exists' => 'Selected product does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',

            'items.*.design_files.max' => 'Maximum 10 design files allowed per item.',
            'items.*.design_files.*.file' => 'Each design file must be a valid file.',
            'items.*.design_files.*.mimes' => 'Design files must be jpeg, png, jpg, gif, webp, pdf, ai, or eps format.',
            'items.*.design_files.*.max' => 'Each design file must not exceed 1MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
//        if (!$this->has('is_gang_sheet')) {
//            $this->merge(['is_gang_sheet' => false]);
//        }

        // If no shipping address provided, use billing address
        if (!$this->has('shipping_address') && $this->has('billing_address')) {
            $this->merge(['shipping_address' => $this->billing_address]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation for gang sheet orders
//            if ($this->is_gang_sheet && count($this->items) < 1) {
//                $validator->errors()->add('items', 'Gang sheet orders must have at least 2 items.');
//            }

            // Validate that products are active
            foreach ($this->items as $index => $item) {
                $product = Product::find($item['product_id']);
                if ($product && !$product->isAvailable()) {
                    $validator->errors()->add("items.{$index}.product_id", 'Selected product is not available.');
                }
            }
        });
    }

//is_gang_sheet:1
//gang_sheet_data[layout_type]:compact
//gang_sheet_data[sheet_size][width]: 24
//gang_sheet_data[sheet_size][height]: 36
//gang_sheet_data[spacing][horizontal]: 0.125
//gang_sheet_data[spacing][vertical]: 0.125
//gang_sheet_data[margin][top]: 0.5
//gang_sheet_data[margin][bottom]: 0.5
//gang_sheet_data[margin][left]: 0.5
//gang_sheet_data[margin][right]: 0.5
//gang_sheet_data[auto_arrange]: true
//gang_sheet_data[cut_lines]: true
//gang_sheet_data[registration_marks]:true
//gang_sheet_data[bleed]:0.125
//gang_sheet_data[rotation_allowed]:true
//gang_sheet_data[priority_order]:size_desc
//gang_sheet_data[notes]:Please optimize for minimal waste. Keep text readable.

}




