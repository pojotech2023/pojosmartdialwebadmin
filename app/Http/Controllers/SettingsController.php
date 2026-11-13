<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TelecallerStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Display settings page with Product Types and Notification Status tabs.
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'products');
        if (!in_array($activeTab, ['products', 'statuses'])) {
            $activeTab = 'products';
        }

        $products = Product::withCount('assignBases')
            ->orderBy('id', 'asc')
            ->get();

        $statuses = TelecallerStatus::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('settings.index', compact('products', 'statuses', 'activeTab'));
    }

    /* =========================================================================
     * PRODUCT TYPES MANAGEMENT
     * ========================================================================= */

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        Product::create([
            'name'        => trim($request->name),
            'description' => trim($request->description),
            'is_active'   => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('settings.index', ['tab' => 'products'])
            ->with('success', 'Product type added successfully!');
    }

    public function updateProduct(Request $request, Product $product)
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

        return redirect()->route('settings.index', ['tab' => 'products'])
            ->with('success', "Product '{$product->name}' updated successfully!");
    }

    public function destroyProduct(Product $product)
    {
        $name = $product->name;
        $product->delete();

        return redirect()->route('settings.index', ['tab' => 'products'])
            ->with('success', "Product '{$name}' deleted successfully!");
    }

    /* =========================================================================
     * NOTIFICATION STATUS MANAGEMENT
     * ========================================================================= */

    public function storeStatus(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255|unique:telecaller_statuses,name',
            'color'                => 'nullable|string|max:30',
            'description'          => 'nullable|string|max:500',
            'requires_appointment' => 'nullable|boolean',
            'requires_location'    => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
            'sort_order'           => 'nullable|integer',
        ]);

        $name = trim($request->name);

        TelecallerStatus::create([
            'name'                 => $name,
            'slug'                 => Str::slug($name),
            'color'                => $request->filled('color') ? trim($request->color) : '#6366f1',
            'description'          => trim($request->description),
            'requires_appointment' => $request->boolean('requires_appointment'),
            'requires_location'    => $request->boolean('requires_location'),
            'is_active'            => $request->has('is_active') ? (bool) $request->is_active : true,
            'sort_order'           => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->route('settings.index', ['tab' => 'statuses'])
            ->with('success', "Notification status '{$name}' created successfully!");
    }

    public function updateStatus(Request $request, TelecallerStatus $telecallerStatus)
    {
        $request->validate([
            'name'                 => 'required|string|max:255|unique:telecaller_statuses,name,' . $telecallerStatus->id,
            'color'                => 'nullable|string|max:30',
            'description'          => 'nullable|string|max:500',
            'requires_appointment' => 'nullable|boolean',
            'requires_location'    => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
            'sort_order'           => 'nullable|integer',
        ]);

        $name = trim($request->name);

        $telecallerStatus->update([
            'name'                 => $name,
            'slug'                 => Str::slug($name),
            'color'                => $request->filled('color') ? trim($request->color) : ($telecallerStatus->color ?: '#6366f1'),
            'description'          => trim($request->description),
            'requires_appointment' => $request->boolean('requires_appointment'),
            'requires_location'    => $request->boolean('requires_location'),
            'is_active'            => $request->has('is_active') ? (bool) $request->is_active : true,
            'sort_order'           => (int) $request->input('sort_order', $telecallerStatus->sort_order),
        ]);

        return redirect()->route('settings.index', ['tab' => 'statuses'])
            ->with('success', "Notification status '{$name}' updated successfully!");
    }

    public function destroyStatus(TelecallerStatus $telecallerStatus)
    {
        $name = $telecallerStatus->name;
        $telecallerStatus->delete();

        return redirect()->route('settings.index', ['tab' => 'statuses'])
            ->with('success', "Notification status '{$name}' deleted successfully!");
    }
}
