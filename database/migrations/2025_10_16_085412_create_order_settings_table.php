<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, json
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('order_settings')->insert([
            [
                'key' => 'tax_rate',
                'label' => 'Tax Rate (%)',
                'value' => '8.5',
                'type' => 'number',
                'description' => 'Default tax rate percentage',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tax_enabled',
                'label' => 'Enable Tax',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable or disable tax calculation',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'flat_shipping_rate',
                'label' => 'Flat Shipping Rate ($)',
                'value' => '10.00',
                'type' => 'number',
                'description' => 'Flat shipping rate for all orders',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'free_shipping_threshold',
                'label' => 'Free Shipping Threshold ($)',
                'value' => '100.00',
                'type' => 'number',
                'description' => 'Minimum order amount for free shipping',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'shipping_method',
                'label' => 'Shipping Method',
                'value' => 'flat', // flat, weight_based, free_threshold
                'type' => 'text',
                'description' => 'Shipping calculation method',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'weight_based_rates',
                'label' => 'Weight Based Shipping Rates',
                'value' => json_encode([
                    ['min_weight' => 0, 'max_weight' => 1, 'rate' => 5.00],
                    ['min_weight' => 1, 'max_weight' => 5, 'rate' => 10.00],
                    ['min_weight' => 5, 'max_weight' => 10, 'rate' => 15.00],
                    ['min_weight' => 10, 'max_weight' => 999, 'rate' => 25.00],
                ]),
                'type' => 'json',
                'description' => 'Shipping rates based on order weight (in kg)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_order_amount',
                'label' => 'Minimum Order Amount ($)',
                'value' => '10.00',
                'type' => 'number',
                'description' => 'Minimum order amount required',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency',
                'label' => 'Currency',
                'value' => 'USD',
                'type' => 'text',
                'description' => 'Default currency code',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_settings');
    }
};
