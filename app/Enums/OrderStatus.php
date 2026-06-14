<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING   = 'pending';
    case PAID      = 'paid';
    case PACKING   = 'packing';
    case SHIPPED   = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'Chờ xác nhận',
            self::PAID      => 'Đã thanh toán',
            self::PACKING   => 'Đang đóng gói',
            self::SHIPPED   => 'Đang giao hàng',
            self::DELIVERED => 'Đã giao hàng',
            self::CANCELLED => 'Đã hủy',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING   => 'yellow',
            self::PAID      => 'blue',
            self::PACKING   => 'purple',
            self::SHIPPED   => 'indigo',
            self::DELIVERED => 'emerald',
            self::CANCELLED => 'red',
        };
    }

    // User chỉ được hủy khi ở 2 trạng thái này
    public function isCancellable(): bool
    {
        return in_array($this, [self::PENDING, self::PAID]);
    }

    // Thứ tự trong timeline
    public function step(): int
    {
        return match($this) {
            self::PENDING   => 1,
            self::PAID      => 2,
            self::PACKING   => 3,
            self::SHIPPED   => 4,
            self::DELIVERED => 5,
            self::CANCELLED => 0,
        };
    }
}