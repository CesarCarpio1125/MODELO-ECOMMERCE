<?php

namespace App\Modules\Orders\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Modules\Orders\DTOs\OrderData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function getFilteredOrders(array $filters, $user): LengthAwarePaginator
    {
        $query = Order::with([
            'customer:id,first_name,last_name,email',
            'items.product:id,name,sku,price',
        ])
            ->where('user_id', $user->id);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getCustomers(): Collection
    {
        return Customer::select('id', 'first_name', 'last_name', 'email')
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get();
    }

    public function getProducts(): Collection
    {
        return Product::select('id', 'name', 'sku', 'price', 'stock_quantity')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function createOrder(array $data, $user): Order
    {
        $orderData = new OrderData($data, $user);

        return DB::transaction(function () use ($orderData) {
            $order = Order::create([
                'user_id' => $orderData->userId,
                'customer_id' => $orderData->customerId,
                'order_number' => $this->generateOrderNumber(),
                'total_amount' => $orderData->totalAmount,
                'tax_amount' => $orderData->taxAmount,
                'shipping_amount' => $orderData->shippingAmount,
                'status' => $orderData->status,
                'payment_status' => 'pending',
                'payment_method' => $orderData->paymentMethod,
                'notes' => $orderData->notes,
            ]);

            foreach ($orderData->items as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData->productId,
                    'quantity' => $itemData->quantity,
                    'unit_price' => $itemData->unitPrice,
                    'total_price' => $itemData->totalPrice,
                ]);
            }

            return $order;
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        $orderData = new OrderData($data, $order->user);

        return DB::transaction(function () use ($order, $orderData) {
            $order->update([
                'customer_id' => $orderData->customerId,
                'total_amount' => $orderData->totalAmount,
                'tax_amount' => $orderData->taxAmount,
                'shipping_amount' => $orderData->shippingAmount,
                'status' => $orderData->status,
                'payment_method' => $orderData->paymentMethod,
                'notes' => $orderData->notes,
            ]);

            // Delete existing items and recreate
            $order->items()->delete();

            foreach ($orderData->items as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData->productId,
                    'quantity' => $itemData->quantity,
                    'unit_price' => $itemData->unitPrice,
                    'total_price' => $itemData->totalPrice,
                ]);
            }

            return $order->fresh(['customer', 'items.product']);
        });
    }

    public function deleteOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $order->items()->delete();

            return $order->delete();
        });
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.strtoupper(uniqid());
    }
}
