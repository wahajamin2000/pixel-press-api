# Pixel Press API Documentation

## Overview

The Pixel Press API is a comprehensive RESTful API built with Laravel for managing a Direct-to-Fabric (DTF) printing business. It supports guest checkout, automatic customer registration, gang sheet functionality, and Stripe payment processing.

## Base URL

```
https://your-domain.com/api/v1
```

## Authentication

The API uses Laravel Sanctum for authentication with different access levels:

- **Public**: No authentication required (product listing, guest checkout)
- **Customer**: Sanctum token with customer role
- **Admin**: Sanctum token with admin role

## Response Format

All API responses follow this consistent format:

```json
{
    "success": true,
    "response_code": 200,
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

For paginated responses:

```json
{
    "success": true,
    "response_code": 200,
    "message": "Success message",
    "data": [...],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75,
        "from": 1,
        "to": 15
    }
}
```

## Error Handling

Error responses include appropriate HTTP status codes and detailed error messages:

```json
{
    "success": false,
    "response_code": 500,
    "message": "Error message",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

## Endpoints

### Products

#### List Products
```http
GET /products
```

**Parameters:**
- `search` (string): Search in name, description, or SKU
- `status` (string): Filter by status (active, inactive, out_of_stock, discontinued)
- `featured` (boolean): Filter featured products
- `sort_by` (string): Sort field (default: sort_order)
- `sort_direction` (string): asc or desc (default: asc)
- `per_page` (integer): Items per page (default: 15)

**Response:**
```json
{
    "success": true,
    "response_code": 200,
    "message": "Products retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Premium Cotton T-Shirt",
            "description": "High-quality cotton t-shirt perfect for DTF printing",
            "short_description": "Premium cotton tee",
            "sku": "COTTON-TEE-001",
            "price": "19.99",
            "formatted_price": "$19.99",
            "weight": "0.25",
            "dimensions": {
                "length": 28,
                "width": 20,
                "height": 0.5
            },
            "material": "100% Cotton",
            "color_options": ["White", "Black", "Gray"],
            "size_options": ["S", "M", "L", "XL"],
            "status": "active",
            "is_featured": true,
            "primary_image_url": "https://example.com/storage/products/tshirt_primary.jpg",
            "all_images_urls": [
                {
                    "id": 1,
                    "url": "https://example.com/storage/products/tshirt_1.jpg",
                    "alt_text": "Premium Cotton T-Shirt - Front View",
                    "is_primary": true,
                    "sort_order": 1
                }
            ],
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

#### Get Featured Products
```http
GET /products/featured
```

**Parameters:**
- `limit` (integer): Number of products to return (default: 8)

#### Search Products
```http
GET /products/search
```

**Parameters:**
- `q` (string, required): Search query (minimum 2 characters)
- `per_page` (integer): Items per page (default: 15)

#### Get Product Details
```http
GET /products/{id}
```

**Response:**
```json
{
    "success": true,
    "response_code": 200,
    "message": "Product retrieved successfully",
    "data": {
        // Full product details with images
    }
}
```

### Orders

#### Create Order (Guest Checkout)
```http
POST /orders
```

**Request Body:**
```json
{
    "customer": {
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "+1234567890"
    },
    "billing_address": {
        "address_line_1": "123 Main St",
        "address_line_2": "Apt 4B",
        "city": "New York",
        "state": "NY",
        "postal_code": "10001",
        "country": "USA"
    },
    "shipping_address": {
        // Same structure as billing_address (optional)
    },
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "design_specifications": {
                "print_area": "front",
                "colors": ["red", "blue"]
            },
            "dimensions": {
                "width": 10,
                "height": 12
            },
            "color_options": ["White"],
            "special_instructions": "Please center the design",
            "gang_sheet_position": {
                "x": 0,
                "y": 0,
                "width": 10,
                "height": 12
            }
        }
    ],
    "notes": "Rush order needed",
    "special_instructions": "Handle with care",
    "is_gang_sheet": false,
    "gang_sheet_data": null
}
```

**Response:**
```json
{
    "success": true,
    "response_code": 200,
    "message": "Order created successfully",
    "data": {
        "id": 1,
        "order_number": "DTF-2024-ABC12345",
        "status": "pending",
        "status_label": "Pending",
        "subtotal": "39.98",
        "tax_amount": "3.40",
        "shipping_amount": "0.00",
        "total_amount": "43.38",
        "formatted_total": "$43.38",
        "currency": "USD",
        "is_gang_sheet": false,
        "customer": {
            "id": 1,
            "full_name": "John Doe",
            "email": "john@example.com"
        },
        "items": [
            {
                "id": 1,
                "product_id": 1,
                "quantity": 2,
                "unit_price": "19.99",
                "total_price": "39.98",
                "formatted_total_price": "$39.98",
                "product_name": "Premium Cotton T-Shirt",
                "product_sku": "COTTON-TEE-001",
                "design_files_urls": []
            }
        ],
        "created_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### Get Order Details
```http
GET /orders/{id}
```

#### Upload Design Files
```http
POST /orders/{orderId}/items/{itemId}/design-files
```

**Request:** Multipart form data
- `design_files[]`: Array of files (max 10 files, 10MB each)

**Supported formats:** JPEG, PNG, GIF, WebP, PDF, AI, EPS

### Payments

#### Create Payment Intent
```http
POST /orders/{id}/payment/intent
```

**Request Body:**
```json
{
    "payment_method_id": "pm_1234567890", // Optional
    "return_url": "https://yoursite.com/payment/return" // Optional
}
```

**Response:**
```json
{
    "success": true,
    "response_code": 200,
    "message": "Payment intent created successfully",
    "data": {
        "client_secret": "pi_1234567890_secret_abc123",
        "payment_intent_id": "pi_1234567890",
        "status": "requires_payment_method",
        "amount": 43.38,
        "currency": "USD"
    }
}
```

#### Confirm Payment
```http
POST /orders/{id}/payment/confirm
```

**Request Body:**
```json
{
    "payment_intent_id": "pi_1234567890"
}
```

#### Get Payment Status
```http
GET /orders/{id}/payment/status
```

#### Stripe Webhook
```http
POST /webhooks/stripe
```

Handles Stripe webhook events for payment status updates.

### Admin Endpoints

All admin endpoints require authentication with admin role.

#### Product Management
```http
POST /admin/products              # Create product
GET /admin/products               # List all products (including inactive)
GET /admin/products/{id}          # Get product details
PUT /admin/products/{id}          # Update product
DELETE /admin/products/{id}       # Delete product
POST /admin/products/{id}/images/{imageId}    # Update product image
DELETE /admin/products/{id}/images/{imageId}  # Delete product image
```

#### Order Management
```http
GET /admin/orders                 # List all orders
PATCH /admin/orders/{id}/status   # Update order status
POST /admin/orders/{id}/cancel    # Cancel order
POST /admin/orders/{id}/payment/refund  # Refund payment
```

### Customer Endpoints

Customer endpoints require authentication with customer role.

```http
GET /customer/orders              # Get customer's orders
GET /customer/orders/{id}         # Get order details
POST /customer/orders/{id}/cancel # Cancel own order
```

## Status Enums

### Product Status
- `active`: Available for purchase
- `inactive`: Not visible to customers
- `out_of_stock`: Visible but not purchasable
- `discontinued`: No longer available

### Order Status
- `pending`: Order created, awaiting payment
- `payment_pending`: Payment processing
- `payment_confirmed`: Payment successful
- `processing`: Order being prepared
- `printing`: Currently printing
- `quality_check`: Quality control phase
- `shipped`: Order shipped
- `delivered`: Order delivered
- `cancelled`: Order cancelled
- `refunded`: Order refunded

## Gang Sheet Functionality

Gang sheets allow customers to combine multiple designs on a single transfer sheet for cost savings. When creating a gang sheet order:

1. Set `is_gang_sheet: true`
2. Include multiple items with `gang_sheet_position` data
3. Provide `gang_sheet_data` with layout information

Example gang sheet item:
```json
{
    "product_id": 1,
    "quantity": 1,
    "gang_sheet_position": {
        "x": 0,
        "y": 0,
        "width": 5,
        "height": 7,
        "rotation": 0
    }
}
```

## File Upload Guidelines

### Product Images
- **Formats:** JPEG, PNG, GIF, WebP, SVG
- **Max size:** 5MB per image
- **Max count:** 10 images per product

### Design Files
- **Formats:** JPEG, PNG, GIF, WebP, PDF, AI, EPS
- **Max size:** 10MB per file
- **Max count:** 10 files per order item

## Error Codes

- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Rate Limiting

API requests are rate-limited to prevent abuse:
- **Public endpoints:** 60 requests per minute
- **Authenticated endpoints:** 100 requests per minute

## Webhooks

### Stripe Webhooks

Configure your Stripe webhook endpoint to: `https://your-domain.com/api/v1/webhooks/stripe`

**Required events:**
- `payment_intent.succeeded`
- `payment_intent.payment_failed`
- `payment_intent.canceled`

## Testing

Use the following test data for development:

### Test Stripe Cards
- **Success:** 4242424242424242
- **Decline:** 4000000000000002
- **3D Secure:** 4000002500003155

### Sample Product Data
```json
{
    "name": "Test Product",
    "sku": "TEST-001",
    "price": 19.99,
    "status": "active",
    "description": "Test product for API testing"
}
```

## Support

For API support and questions, please contact the development team or refer to the project repository.
