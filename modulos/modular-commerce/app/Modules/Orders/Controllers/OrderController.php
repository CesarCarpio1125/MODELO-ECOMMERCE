<?php

namespace App\Modules\Orders\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Modules\Orders\Requests\StoreOrderRequest;
use App\Modules\Orders\Requests\UpdateOrderRequest;
use App\Modules\Orders\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);
        $orders = $this->orderService->getFilteredOrders($filters, $request->user());

        return inertia('Orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return inertia('Orders/Create', [
            'customers' => $this->orderService->getCustomers(),
            'products' => $this->orderService->getProducts(),
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated(), $request->user());

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Order created successfully!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create order: '.$e->getMessage())
                ->withInput();
        }
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return inertia('Orders/Show', [
            'order' => $order->load(['customer', 'items.product']),
        ]);
    }

    public function edit(Order $order)
    {
        $this->authorize('update', $order);

        return inertia('Orders/Edit', [
            'order' => $order->load(['customer', 'items.product']),
            'customers' => $this->orderService->getCustomers(),
            'products' => $this->orderService->getProducts(),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->authorize('update', $order);

        try {
            $this->orderService->updateOrder($order, $request->validated());

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Order updated successfully!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update order: '.$e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        try {
            $this->orderService->deleteOrder($order);

            return redirect()
                ->route('orders.index')
                ->with('success', 'Order deleted successfully!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete order: '.$e->getMessage());
        }
    }
}
