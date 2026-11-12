<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('telecaller_statuses')) {
            Schema::create('telecaller_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->nullable();
                $table->string('color', 20)->default('#6366f1');
                $table->text('description')->nullable();
                $table->boolean('requires_appointment')->default(false);
                $table->boolean('requires_location')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed initial telecaller statuses requested by user
            $statuses = [
                [
                    'name'                 => 'Quotation Shared',
                    'slug'                 => 'quotation-shared',
                    'color'                => '#2563eb', // Blue
                    'description'          => 'Quotation / proposal has been shared with the customer',
                    'requires_appointment' => false,
                    'requires_location'    => false,
                    'is_active'            => true,
                    'sort_order'           => 1,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
                [
                    'name'                 => 'Appointment-Online',
                    'slug'                 => 'appointment-online',
                    'color'                => '#7c3aed', // Purple
                    'description'          => 'Online demo / Google Meet / Zoom meeting scheduled',
                    'requires_appointment' => true,
                    'requires_location'    => true,
                    'is_active'            => true,
                    'sort_order'           => 2,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
                [
                    'name'                 => 'Appointment-Offline',
                    'slug'                 => 'appointment-offline',
                    'color'                => '#059669', // Emerald
                    'description'          => 'In-person / on-site customer visit scheduled',
                    'requires_appointment' => true,
                    'requires_location'    => true,
                    'is_active'            => true,
                    'sort_order'           => 3,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
                [
                    'name'                 => 'Follow Up',
                    'slug'                 => 'follow-up',
                    'color'                => '#d97706', // Amber
                    'description'          => 'Customer requested follow-up / callback at a later time',
                    'requires_appointment' => false,
                    'requires_location'    => false,
                    'is_active'            => true,
                    'sort_order'           => 4,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
                [
                    'name'                 => 'Confirmed',
                    'slug'                 => 'confirmed',
                    'color'                => '#16a34a', // Green
                    'description'          => 'Customer confirmed interest / order closure',
                    'requires_appointment' => false,
                    'requires_location'    => false,
                    'is_active'            => true,
                    'sort_order'           => 5,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
                [
                    'name'                 => 'Nope',
                    'slug'                 => 'nope',
                    'color'                => '#dc2626', // Red
                    'description'          => 'Customer not interested / rejected',
                    'requires_appointment' => false,
                    'requires_location'    => false,
                    'is_active'            => true,
                    'sort_order'           => 6,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
            ];

            DB::table('telecaller_statuses')->insert($statuses);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telecaller_statuses');
    }
};
