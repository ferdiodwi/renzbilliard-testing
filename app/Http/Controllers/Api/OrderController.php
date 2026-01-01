<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SessionBilliard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Store F&B order for a specific session
     */
    public function store(Request $request, $sessionId)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $session = SessionBilliard::findOrFail($sessionId);

        // if ($session->status !== 'playing') {
        //     return response()->json(['message' => 'Session is not active'], 400);
        // }

        try {
            DB::beginTransaction();

            // Check if there is already an open order for this session
            $order = Order::where('session_id', $sessionId)
                ->where('status', 'pending')
                ->first();

            if (!$order) {
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'session_id' => $sessionId,
                    'customer_name' => $session->customer_name, // Sync customer name
                    'cashier_id' => auth()->id(),
                    'status' => 'pending',
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                ]);
            }

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Add or update item in order
                $existingItem = OrderItem::where('order_id', $order->id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($existingItem) {
                    $existingItem->quantity += $item['quantity'];
                    $existingItem->subtotal = $existingItem->quantity * $existingItem->price;
                    $existingItem->save();
                } else {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'subtotal' => $item['quantity'] * $product->price,
                    ]);
                }
                
                // Reduce stock if tracked
                if ($product->stock !== null) {
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi (Sisa: {$product->stock})");
                    }
                    $product->decrement('stock', $item['quantity']);
                }
            }

            // Recalculate totals
            $subtotal = $order->items()->sum('subtotal');
            $tax = 0; // Or whatever tax logic
            $total = $subtotal + $tax;

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order added to session successfully',
                'data' => $order->load('items.product'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Order::with(['session.table', 'items.product'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        } else {
            // By default, exclude cancelled orders
            $query->whereIn('status', ['pending', 'completed']);
        }

        // Search by customer name or table number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('session', function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('table', function($tableQuery) use ($search) {
                      $tableQuery->where('table_number', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;

        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get order details for a session
     */
    public function show($sessionId)
    {
        $order = Order::where('session_id', $sessionId)
            ->where('status', 'pending')
            ->with('items.product')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Remove item from order
     */
    public function destroyItem($sessionId, $itemId)
    {
        try {
            DB::beginTransaction();
            
            $order = Order::where('session_id', $sessionId)
                ->where('status', 'pending')
                ->firstOrFail();

            $item = OrderItem::where('order_id', $order->id)
                ->where('id', $itemId)
                ->firstOrFail();

            $item->delete();

            // Restore stock if tracked
            if ($item->product->stock !== null) {
                $item->product->increment('stock', $item->quantity);
            }

            // Recalculate
            $subtotal = $order->items()->sum('subtotal');
            $tax = 0;
            $total = $subtotal + $tax;

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);
            
            // If no items left, might want to keep the order or delete it?
            // Keeping it is fine.

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item removed',
                'data' => $order->load('items.product')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to remove item: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Delete order (and restore stock)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            // Soft delete: Set status to cancelled instead of deleting
            // Stock is NOT restored (already consumed or counted as loss)
            $order->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus order: ' . $e->getMessage(),
            ], 500);
        }
    }
}
