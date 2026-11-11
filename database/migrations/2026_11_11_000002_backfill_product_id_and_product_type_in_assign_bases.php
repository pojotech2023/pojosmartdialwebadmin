<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AssignBase;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure default active product exists
        $defaultProduct = Product::where('is_active', true)->first();
        if (!$defaultProduct) {
            $defaultProduct = Product::firstOrCreate(
                ['name' => 'Website'],
                ['is_active' => true, 'description' => 'Default product']
            );
        }

        // 1. Where product_type is set but product_id is null: link product_id
        $basesWithTypeNameOnly = AssignBase::whereNull('product_id')->whereNotNull('product_type')->get();
        foreach ($basesWithTypeNameOnly as $b) {
            $name = trim($b->product_type);
            if ($name !== '') {
                $p = Product::firstOrCreate(
                    ['name' => $name],
                    ['is_active' => true]
                );
                $b->update([
                    'product_id'   => $p->id,
                    'product_type' => $p->name,
                ]);
            }
        }

        // 2. Where product_id is set but product_type is null: sync product_type name
        $basesWithIdOnly = AssignBase::whereNotNull('product_id')->whereNull('product_type')->get();
        foreach ($basesWithIdOnly as $b) {
            $p = Product::find($b->product_id);
            if ($p) {
                $b->update(['product_type' => $p->name]);
            }
        }

        // 3. Where BOTH product_id and product_type are null: assign default active product
        AssignBase::whereNull('product_id')->whereNull('product_type')->update([
            'product_id'   => $defaultProduct->id,
            'product_type' => $defaultProduct->name,
        ]);

        // 4. In case product_type is empty string
        AssignBase::where('product_type', '')->update([
            'product_id'   => $defaultProduct->id,
            'product_type' => $defaultProduct->name,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for data backfill
    }
};
