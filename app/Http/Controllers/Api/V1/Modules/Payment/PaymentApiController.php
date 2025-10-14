<?php

namespace App\Http\Controllers\Api\V1\Modules\Payment;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Modules\Order\OrderResource;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentApiController extends Controller
{
    public function __construct()
    {
        // Set Stripe API key
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create payment intent for an order
     */
    public function createPaymentIntent(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'payment_method_id' => 'nullable|string',
            'return_url' => 'nullable|url',
        ]);

        try {
            // Check if order already has a payment intent
            if ($order->payment_intent_id) {
                $paymentIntent = PaymentIntent::retrieve($order->payment_intent_id);

                // If payment intent is already succeeded, return error
                if ($paymentIntent->status === 'succeeded') {
                    return $this->successResponse('Payment has already been completed for this order', []);
                }
            } else {
                // Create new payment intent
                $paymentIntent = PaymentIntent::create([
                    'amount' => $this->convertToStripeAmount($order->total_amount),
                    'currency' => strtolower($order->currency),
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer_email' => optional($order->user)->email,
                    ],
                    'description' => "Payment for order {$order->order_number}",
                    'receipt_email' => optional($order->user)->email,
                ]);

                // Update order with payment intent ID
                $order->update([
                    'payment_intent_id' => $paymentIntent->id,
                    'payment_status' => PaymentStatus::PENDING,
                ]);
            }

            // If payment method is provided, confirm the payment
            if ($request->payment_method_id) {
                $paymentIntent = PaymentIntent::retrieve($paymentIntent->id);
                $paymentIntent->confirm([
                    'payment_method' => $request->payment_method_id,
                    'return_url' => $request->return_url ?? config('app.url') . '/payment/return',
                ]);
            }

            return $this->successResponse('Payment intent created successfully', [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $order->total_amount,
                'currency' => $order->currency,
            ]);

        } catch (ApiErrorException $e) {
            return $this->errorResponse('Payment processing failed: ' . $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while processing payment', 500);
        }
    }

    /**
     * Confirm payment for an order
     */
    public function confirmPayment(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            // Verify payment intent belongs to this order
            if ($paymentIntent->metadata->order_id != $order->id) {
                return $this->errorResponse('Payment intent does not match this order', 400);
            }

            // Update order based on payment status
            switch ($paymentIntent->status) {
                case 'succeeded':
                    $order->update([
                        'payment_status' => PaymentStatus::PAID,
                        'payment_method' => $paymentIntent->payment_method_types[0] ?? 'card',
                    ]);
                    break;

                case 'requires_action':
                case 'requires_source_action':
                    return $this->successResponse('Payment requires additional action', [
                        'requires_action' => true,
                        'client_secret' => $paymentIntent->client_secret,
                    ]);

                case 'requires_payment_method':
                    return $this->errorResponse('Payment method was declined', 400);

                case 'canceled':
                    $order->update(['payment_status' => PaymentStatus::FAILED]);
                    return $this->errorResponse('Payment was cancelled', 400);

                default:
                    return $this->errorResponse('Payment is in an unexpected state', 400);
            }

            $order->load(['user', 'items.product']);

            return $this->successResponse('Payment confirmed successfully',[
                'order' => new OrderResource($order),
            ] );

        } catch (ApiErrorException $e) {
            return $this->errorResponse('Payment confirmation failed: ' . $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while confirming payment', 500);
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            Log::info('webhook payment event', ['event' => $event]);

        } catch (\UnexpectedValueException $e) {
            return $this->errorResponse('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return $this->errorResponse('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            case 'payment_intent.canceled':
                $this->handlePaymentCanceled($event->data->object);
                break;

            default:
                Log::info('Unhandled Stripe webhook event: ' . $event->type);
        }

        return $this->successResponse('Webhook handled successfully', null);
    }

    /**
     * Get payment status for an order
     */
    public function getPaymentStatus(Order $order): JsonResponse
    {
        if (!$order->payment_intent_id) {
            return $this->errorResponse('No payment intent found for this order', 404);
        }

        try {
            $paymentIntent = PaymentIntent::retrieve($order->payment_intent_id);

            return $this->successResponse('Payment status retrieved successfully', [
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $this->convertFromStripeAmount($paymentIntent->amount),
                'currency' => strtoupper($paymentIntent->currency),
                'payment_method' => $paymentIntent->payment_method_types[0] ?? null,
                'created' => $paymentIntent->created,
            ]);

        } catch (ApiErrorException $e) {
            return $this->errorResponse('Failed to retrieve payment status: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Refund payment for an order
     */
    public function refundPayment(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|in:duplicate,fraudulent,requested_by_customer',
        ]);

        if (!$order->payment_intent_id) {
            return $this->errorResponse('No payment found for this order', 404);
        }

        try {
            $refundData = [
                'payment_intent' => $order->payment_intent_id,
                'reason' => $request->reason ?? 'requested_by_customer',
            ];

            // If partial refund amount is specified
            if ($request->amount) {
                $refundData['amount'] = $this->convertToStripeAmount($request->amount);
            }

            $refund = \Stripe\Refund::create($refundData);

            // Update order status
            $order->update(['status' => OrderStatus::REFUNDED]);

            return $this->successResponse('Refund processed successfully', [
                'refund_id' => $refund->id,
                'amount' => $this->convertFromStripeAmount($refund->amount),
                'status' => $refund->status,
            ]);

        } catch (ApiErrorException $e) {
            return $this->errorResponse('Refund failed: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSucceeded($paymentIntent): void
    {
        Log::info('handlePaymentSucceeded', ['paymentIntent' => $paymentIntent]);

        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();

        if ($order) {
            $order->update([
                'payment_status' => PaymentStatus::PAID,
                'payment_method' => $paymentIntent->payment_method_types[0] ?? 'card',
            ]);

            Log::info('Payment succeeded for order', ['order_id' => $order->id]);
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed($paymentIntent): void
    {
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();

        if ($order) {
            $order->update([
                'payment_status' => PaymentStatus::FAILED,
            ]);

            Log::warning('Payment failed for order', ['order_id' => $order->id]);
        }
    }

    /**
     * Handle canceled payment
     */
    private function handlePaymentCanceled($paymentIntent): void
    {
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();

        if ($order) {
            $order->update(['status' => OrderStatus::CANCELLED]);

            Log::info('Payment canceled for order', ['order_id' => $order->id]);
        }
    }

    /**
     * Convert amount to Stripe format (cents)
     */
    private function convertToStripeAmount(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert amount from Stripe format (cents)
     */
    private function convertFromStripeAmount(int $amount): float
    {
        return $amount / 100;
    }
}
