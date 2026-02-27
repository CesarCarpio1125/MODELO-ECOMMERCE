<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderService
{
    public function getFilteredOrders(array $filters, $user): LengthAwarePaginator
    {
        $query = Order::with([
            'customer:id,first_name,last_name,email',
            'items.product:id,name,sku,price',
            'items' => function ($query) {
                $query->select('id', 'order_id', 'product_id', 'quantity', 'unit_price');
            },
        ]);

        // Apply user filter if user is provided
        if ($user && $user->id) {
            $query->where('user_id', $user->id);
        }

        // Apply filters efficiently
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id']) && $filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Add select for performance optimization
        return $query->select([
            'id', 'user_id', 'customer_id', 'order_number',
            'total_amount', 'tax_amount', 'shipping_amount',
            'status', 'payment_status', 'payment_method',
            'notes', 'created_at', 'updated_at',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
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
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();
    }

    public function createOrder(array $data, $user): Order
    {
        $orderData = [
            'user_id' => $user->id,
            'customer_id' => $data['customer_id'],
            'order_number' => $this->generateOrderNumber(),
            'total_amount' => 0,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'] ?? null,
        ];

        $order = Order::create($orderData);

        $this->createOrderItems($order, $data['items']);

        $this->updateOrderTotals($order);

        return $order;
    }

    public function updateOrder(Order $order, array $data): void
    {
        $order->update([
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
            'notes' => $data['notes'] ?? $order->notes,
            'shipped_at' => $data['shipped_at'] ?? null,
            'delivered_at' => $data['delivered_at'] ?? null,
        ]);
    }

    public function deleteOrder(Order $order): void
    {
        $order->items()->delete();
        $order->delete();
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.strtoupper(uniqid());
    }

    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product) {
                throw new \Exception("Product with ID {$item['product_id']} not found");
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
                'product_attributes' => $product->attributes ?? null,
            ]);
        }
    }

    private function updateOrderTotals(Order $order): void
    {
        $totals = $order->items()->selectRaw('
                SUM(quantity * unit_price) as total_amount,
                SUM(quantity * unit_price * 0.1) as tax_amount
            ')->first();

        $order->update([
            'total_amount' => $totals->total_amount,
            'tax_amount' => $totals->tax_amount ?? 0,
        ]);
    }
}
