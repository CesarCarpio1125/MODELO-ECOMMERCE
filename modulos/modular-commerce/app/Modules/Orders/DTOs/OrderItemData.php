<?php

namespace App\Modules\Orders\DTOs;

class OrderItemData
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
        public readonly float $unitPrice
    ) {
        $this->validate();
    }

    public function getTotalPrice(): float
    {
        return $this->quantity * $this->unitPrice;
    }

    private function validate(): void
    {
        if ($this->quantity <= 0) {
            throw new \InvalidArgumentException('Item quantity must be greater than 0');
        }

        if ($this->unitPrice < 0) {
            throw new \InvalidArgumentException('Item unit price cannot be negative');
        }
    }
}
