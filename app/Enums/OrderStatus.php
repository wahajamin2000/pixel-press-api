<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAYMENT_PENDING = 'payment_pending';
    case PAYMENT_CONFIRMED = 'payment_confirmed';
    case PAYMENT_CANCELLED = 'payment_cancelled';
    case PAYMENT_FAILED = 'payment_failed';
    case PROCESSING = 'processing';
    case PRINTING = 'printing';
    case QUALITY_CHECK = 'quality_check';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PAYMENT_PENDING => 'Payment Pending',
            self::PAYMENT_CONFIRMED => 'Payment Confirmed',
            self::PAYMENT_CANCELLED => 'Payment Cancelled',
            self::PROCESSING => 'Processing',
            self::PRINTING => 'Printing',
            self::QUALITY_CHECK => 'Quality Check',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::PAYMENT_PENDING => 'orange',
            self::PAYMENT_CONFIRMED => 'blue',
            self::PAYMENT_CANCELLED => 'danger',
            self::PROCESSING => 'indigo',
            self::PRINTING => 'purple',
            self::QUALITY_CHECK => 'pink',
            self::SHIPPED => 'cyan',
            self::DELIVERED => 'green',
            self::CANCELLED => 'red',
            self::REFUNDED => 'gray',
        };
    }

    public function isActive(): bool
    {
        return !in_array($this, [self::CANCELLED, self::REFUNDED, self::DELIVERED]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::PAYMENT_PENDING,
            self::PAYMENT_CONFIRMED,
            self::PROCESSING
        ]);
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
