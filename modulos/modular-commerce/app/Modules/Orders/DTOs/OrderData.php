<?php

namespace App\Modules\Orders\DTOs;

class OrderData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $customerId,
        public readonly array $items,
        public readonly string $paymentMethod,
        public readonly ?string $notes = null,
        public readonly string $status = 'pending',
        public readonly float $taxAmount = 0.0,
        public readonly float $shippingAmount = 0.0
    ) {
        $this->validateItems();
    }

    public function getTotalAmount(): float
    {
        return collect($this->items)->sum(fn (OrderItemData $item) => $item->totalPrice) + $this->taxAmount + $this->shippingAmount;
    }

    private function validateItems(): void
    {
        if (empty($this->items)) {
            throw new \InvalidArgumentException('Order must have at least one item');
        }

        foreach ($this->items as $item) {
            if (! $item instanceof OrderItemData) {
                throw new \InvalidArgumentException('All items must be OrderItemData instances');
            }
        }
    }
}
