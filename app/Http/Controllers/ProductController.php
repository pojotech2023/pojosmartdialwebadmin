<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        $products = Product::withCount('assignBases')
            ->orderBy('id', 'asc')
            ->get();

        return view('products.index', compact('products'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string|max:500',
        ]);

        Product::create([
            'name'        => trim($request->name),
            'description' => trim($request->description),
            'is_active'   => true,
        ]);

        return redirect()->route('products.index')->with('success', 'Product added successfully! It will now appear in the mobile dropdown and Assign Base.');
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        $product->update([
            'name'        => trim($request->name),
            'description' => trim($request->description),
            'is_active'   => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        return redirect()->route('products.index')->with('success', "Product '{$name}' deleted successfully!");
    }
}
