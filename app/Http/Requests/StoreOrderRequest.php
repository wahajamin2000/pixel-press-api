<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Customer information
            'customer' => 'required|array',
            'customer.first_name' => 'required|string|max:255',
            'customer.last_name' => 'nullable|string|max:255',
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
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:1000',
            'items.*.design_specifications' => 'nullable|array',
            'items.*.dimensions' => 'nullable|array',
            'items.*.dimensions.width' => 'nullable|numeric|min:0',
            'items.*.dimensions.height' => 'nullable|numeric|min:0',
            'items.*.color_options' => 'nullable|array',
            'items.*.special_instructions' => 'nullable|string|max:1000',

            // Design files - Multiple approaches supported
            // Method 1: Files nested in items array
            'items.*.design_files' => 'nullable|array|max:10',
            'items.*.design_files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf,ai,eps,svg|max:10240',

            // Method 3: General design files array
//            'design_files' => 'nullable|array',
//            'design_files.*' => 'sometimes|file|mimes:jpeg,png,jpg,gif,webp,pdf,ai,eps,svg|max:10240',

            // Order metadata
            'notes' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:1000',
            'shipping_method' => 'nullable|string|in:standard,express,overnight',
            'discount_code' => 'nullable|string|max:50',
            'custom_discount_amount' => 'nullable|numeric|min:0',
            'tax_exempt' => 'nullable|boolean',

            // Gang sheet options (if applicable)
//            'is_gang_sheet' => 'nullable|boolean',
//            'gang_sheet_data' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'At least one item is required for the order.',
            'items.*.product_id.required' => 'Product is required for each item.',
            'items.*.product_id.exists' => 'Selected product does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.design_files.*.file' => 'Each design file must be a valid file.',
            'items.*.design_files.*.mimes' => 'Design files must be: jpeg, png, jpg, gif, webp, pdf, ai, eps, or svg.',
            'items.*.design_files.*.max' => 'Each design file must not exceed 10MB.',
//            'design_files.*.file' => 'Each design file must be a valid file.',
//            'design_files.*.mimes' => 'Design files must be: jpeg, png, jpg, gif, webp, pdf, ai, eps, or svg.',
//            'design_files.*.max' => 'Each design file must not exceed 10MB.',
        ];
    }

    protected function prepareForValidation()
    {
        // Ensure items array exists
        if (!$this->has('items')) {
            $this->merge(['items' => []]);
        }

        // Convert string numbers to integers where needed
        if ($this->has('items')) {
            $items = $this->input('items');
            foreach ($items as $index => $item) {
                if (isset($item['quantity'])) {
                    $items[$index]['quantity'] = (int) $item['quantity'];
                }
                if (isset($item['product_id'])) {
                    $items[$index]['product_id'] = (int) $item['product_id'];
                }
            }
            $this->merge(['items' => $items]);
        }
    }
}
