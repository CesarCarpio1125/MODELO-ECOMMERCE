<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function myOrders(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to']);

        // Only add user_id filter if user is authenticated
        if ($request->user()) {
            $filters['user_id'] = $request->user()->id;
        }

        $orders = $this->orderService->getFilteredOrders($filters, $request->user());

        return inertia('Orders/My', [
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
            DB::beginTransaction();

            $order = $this->orderService->createOrder($request->validated(), $request->user());

            DB::commit();

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

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
            'canUpdateStatus' => auth()->user()->can('updateStatus', $order),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->authorize('update', $order);

        // Check if status is being changed and validate permission
        if (isset($request->validated()['status']) && $request->validated()['status'] !== $order->status) {
            $this->authorize('updateStatus', $order);
        }

        try {
            DB::beginTransaction();

            $this->orderService->updateOrder($order, $request->validated());

            DB::commit();

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Order updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Failed to update order: '.$e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        try {
            DB::beginTransaction();

            $this->orderService->deleteOrder($order);

            DB::commit();

            return redirect()
                ->route('orders.index')
                ->with('success', 'Order deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Failed to delete order: '.$e->getMessage());
        }
    }
}
