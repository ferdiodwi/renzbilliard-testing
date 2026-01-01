<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by availability
        if ($request->has('available')) {
            $query->available();
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Get per_page from request with validation
        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;

        $products = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'is_available' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $filename);
            $validated['image'] = $filename;
        }

        $product = Product::create($validated);
        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Display the specified product
     */
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'category_id' => 'exists:categories,id',
            'price' => 'numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'is_available' => 'boolean',
        ]);

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image == '1') {
            // Delete old image if exists
            if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
                unlink(public_path('images/products/' . $product->image));
            }
            $validated['image'] = null;
        }
        // Handle image upload
        elseif ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
                unlink(public_path('images/products/' . $product->image));
            }
            
            $image = $request->file('image');
            $filename = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $filename);
            $validated['image'] = $filename;
        }

        $product->update($validated);
        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete image if exists
        if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
            unlink(public_path('images/products/' . $product->image));
        }
        
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
