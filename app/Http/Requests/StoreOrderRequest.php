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

            // EITHER product OR gangsheet object required
            'items.*.product' => 'required_without:items.*.gangsheet|array',
            'items.*.gangsheet' => 'required_without:items.*.product|array',

            // Product object validation
            'items.*.product.id' => 'required_with:items.*.product|exists:products,id',
            'items.*.product.name' => 'nullable|string|max:255',
            'items.*.product.sku' => 'nullable|string|max:255',
            'items.*.product.quantity' => 'required_with:items.*.product|integer|min:1|max:1000',
            'items.*.product.unit_price' => 'required_with:items.*.product|numeric|min:0',
            'items.*.product.dimensions' => 'nullable|array',
            'items.*.product.dimensions.width' => 'nullable|numeric|min:0',
            'items.*.product.dimensions.height' => 'nullable|numeric|min:0',
            'items.*.product.size_options' => 'nullable|array',
            'items.*.product.size_options.size' => 'nullable|string|max:50',
            'items.*.product.size_options.additional_charge' => 'nullable|numeric|min:0',
            'items.*.product.color_options' => 'nullable|array',
            'items.*.product.color_options.color' => 'nullable|string|max:50',
            'items.*.product.color_options.additional_charge' => 'nullable|numeric|min:0',
            'items.*.product.special_instructions' => 'nullable|string|max:1000',
            'items.*.product.design_specifications' => 'nullable|array',
            'items.*.product.design_files' => 'nullable|array|max:10',
            'items.*.product.design_files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf,ai,eps,svg|max:10240',

            // Gangsheet object - present means this is a gangsheet item
            'items.*.gangsheet.id' => 'required_with:items.*.gangsheet|string',
            'items.*.gangsheet.name' => 'nullable',
            'items.*.gangsheet.thumbnail_url' => 'nullable|string|url',
            'items.*.gangsheet.size' => 'required_with:items.*.gangsheet|array',
            'items.*.gangsheet.size.id' => 'required_with:items.*.gangsheet.size|integer',
            'items.*.gangsheet.size.title' => 'required_with:items.*.gangsheet.size|string',
            'items.*.gangsheet.size.width' => 'required_with:items.*.gangsheet.size|numeric',
            'items.*.gangsheet.size.height' => 'required_with:items.*.gangsheet.size|numeric',
            'items.*.gangsheet.size.unit' => 'nullable|string|in:in,cm',
            'items.*.gangsheet.quantity' => 'required_with:items.*.gangsheet|integer|min:1|max:1000',
            'items.*.gangsheet.unit_price' => 'required_with:items.*.gangsheet|numeric|min:0',
            'items.*.gangsheet.special_instructions' => 'nullable|string|max:1000',

            // Order metadata
            'notes' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:1000',
            'shipping_method' => 'nullable|string|in:standard,express,overnight',
            'discount_code' => 'nullable|string|max:50',
            'custom_discount_amount' => 'nullable|numeric|min:0',

            'tax_exempt' => 'nullable|boolean',
            'subtotal' => 'required|numeric',
            'tax_amount' => 'nullable|numeric',
            'shipping_amount' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'total_amount' => 'required|numeric',

        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'At least one item is required for the order.',
            'items.*.product.required_without' => 'Either product or gangsheet must be provided for each item.',
            'items.*.gangsheet.required_without' => 'Either product or gangsheet must be provided for each item.',
            'items.*.product.id.required_with' => 'Product ID is required when product is provided.',
            'items.*.product.id.exists' => 'Selected product does not exist.',
            'items.*.product.quantity.required_with' => 'Quantity is required when product is provided.',
            'items.*.product.quantity.min' => 'Product quantity must be at least 1.',
            'items.*.product.unit_price.required_with' => 'Unit price is required when product is provided.',
            'items.*.gangsheet.id.required_with' => 'Gangsheet ID is required when gangsheet is provided.',
            'items.*.gangsheet.quantity.required_with' => 'Quantity is required when gangsheet is provided.',
            'items.*.gangsheet.unit_price.required_with' => 'Unit price is required when gangsheet is provided.',
            'items.*.product.design_files.*.file' => 'Each design file must be a valid file.',
            'items.*.product.design_files.*.mimes' => 'Design files must be: jpeg, png, jpg, gif, webp, pdf, ai, eps, or svg.',
            'items.*.product.design_files.*.max' => 'Each design file must not exceed 10MB.',


        ];
    }


    protected function prepareForValidation()
    {
        // Ensure items array exists
        if (!$this->has('items')) {
            $this->merge(['items' => []]);
            return;
        }

        $items = $this->input('items');

        foreach ($items as $index => $item) {
            // Determine item type based on which object is present
            $hasProduct = isset($item['product']) && is_array($item['product']);
            $hasGangsheet = isset($item['gangsheet']) && is_array($item['gangsheet']);

            // Skip if neither is present (validation will catch this)
            if (!$hasProduct && !$hasGangsheet) {
                continue;
            }

            // Convert numeric strings to proper types for product
            if ($hasProduct) {
                if (isset($item['product']['quantity'])) {
                    $items[$index]['product']['quantity'] = (int) $item['product']['quantity'];
                }
                if (isset($item['product']['id'])) {
                    $items[$index]['product']['id'] = (int) $item['product']['id'];
                }
                if (isset($item['product']['unit_price'])) {
                    $items[$index]['product']['unit_price'] = (float) $item['product']['unit_price'];
                }
            }

            // Convert numeric strings to proper types for gangsheet
            if ($hasGangsheet) {
                if (isset($item['gangsheet']['quantity'])) {
                    $items[$index]['gangsheet']['quantity'] = (int) $item['gangsheet']['quantity'];
                }
                if (isset($item['gangsheet']['unit_price'])) {
                    $items[$index]['gangsheet']['unit_price'] = (float) $item['gangsheet']['unit_price'];
                }
                if (isset($item['gangsheet']['size'])) {
                    if (isset($item['gangsheet']['size']['id'])) {
                        $items[$index]['gangsheet']['size']['id'] = (int) $item['gangsheet']['size']['id'];
                    }
                    if (isset($item['gangsheet']['size']['width'])) {
                        $items[$index]['gangsheet']['size']['width'] = (float) $item['gangsheet']['size']['width'];
                    }
                    if (isset($item['gangsheet']['size']['height'])) {
                        $items[$index]['gangsheet']['size']['height'] = (float) $item['gangsheet']['size']['height'];
                    }
                }
            }
        }

        $this->merge(['items' => $items]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes()
    {
        return [
            'items.*.product.id' => 'product ID',
            'items.*.product.quantity' => 'product quantity',
            'items.*.product.unit_price' => 'product unit price',
            'items.*.gangsheet.id' => 'gangsheet ID',
            'items.*.gangsheet.quantity' => 'gangsheet quantity',
            'items.*.gangsheet.unit_price' => 'gangsheet unit price',
        ];
    }
}
